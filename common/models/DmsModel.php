<?php

namespace common\models;

use common\helpers\AifinHelper;
use common\helpers\XMcrypt;
use common\models\Document;
use common\models\DocumentFolder;
use common\models\WebLang;
use yii\base\Exception;
use yii\base\Model;
use Yii;

class DmsModel extends Model {

    public $dir;
//    public $idfolder; // idfolder to be written to document table
    public $documents; // array of iddocument

    public function init() {
        $this->dir = Yii::$app->params['dmsPath'];
    }

    /**
     * This function process the POST variables for data
     * @param array $params
     * @return boolean
     */
    public function upload($params) {

        $this->dir = Yii::$app->params['dmsPath'];

        $idfolder = 1; // Document table bugtracker documents

        if (!DocumentFolder::findOne($idfolder)) {
            $this->addError('app', $msg = "Invalid idfolder $idfolder");
            Yii::error($msg);
            return FALSE;
        }

        Yii::trace("uploading files to idfolder = $idfolder");
   
        if (!isset($_FILES["file"])) {
            Yii::error($msg = "Invalid file uploading, abort");
            $this->addError('app', $msg);
            return FALSE;
        }

        $validUpload = TRUE;

// try to convert single upload to multiple upload format
        if (!isset($_FILES["file"]["error"][0])) {
            $files["file"]["error"][0] = $_FILES["file"]["error"];
            $files["file"]["tmp_name"][0] = $_FILES["file"]["tmp_name"];
            $files["file"]["name"][0] = $_FILES["file"]["name"];
            $files["file"]["size"][0] = $_FILES["file"]["size"];
            $files["file"]["type"][0] = $_FILES["file"]["type"];
        } else {
            $files = $_FILES;
        }

        foreach ($files["file"]["error"] as $key => $error) {
         
            if ($error == UPLOAD_ERR_OK) {
                $file_tmp = $files["file"]["tmp_name"][$key];
                $file_name = basename($files["file"]["name"][$key]);
                $file_size = $files["file"]['size'][$key];
                $file_type = $files["file"]['type'][$key];

                if (empty($file_name)) {
                    $validUpload = false;
                }
                if ($file_size > AifinHelper::file_upload_max_size("byte")) {
                    $errors[] = 'File size must be less than ' . AifinHelper::file_upload_max_size("kb") . 'kb';
                }
                if (is_dir($this->dir) == false) {
                    mkdir($this->dir, 0775);  // Create directory if it does not exist
                }

                $random_file_name = AifinHelper::genFileName();

                /*
                 * Get the fs_folder_name from DocumentFolder
                 */
                $fsFolderName = DocumentFolder::find()->select("fs_folder_name")->where(['id' => $idfolder, 'status' => 1])->scalar();

                if (!empty($fsFolderName)) {
                    if (is_dir($this->dir . "/" . $fsFolderName) == false) {
                        mkdir($this->dir . "/" . $fsFolderName, 0775);  // Create sub directory if it does not exist
                    }

                    $destFile = $this->dir . "/" . $fsFolderName . "/" . $random_file_name;
                } else {
                    $destFile = $this->dir . $random_file_name;
                }

                if (is_dir($destFile) == false) {
                    move_uploaded_file($file_tmp, $destFile);

//                    /* encrypt the file */
//                    XMcrypt::encrypt($destFile);
                } else {         //rename the file if another one exist
                    $new_dir = $destFile;
                    rename($file_tmp, $new_dir);
                }

                $document = new Document();
                $document->file_name = $random_file_name;
                $document->orig_file_name = $file_name;
                $document->file_size = (string) $file_size;
                $document->file_type = $file_type;
                $document->idfolder = $idfolder;
                $document->token = AifinHelper::genRandomKey(64);

                if (isset($params['idcrm_activity'])) {
                    $document->idca = $params['idcrm_activity'];
                }
                if (isset($params['idcrm_record'])) {
                    $document->idcr = $params['idcrm_record'];
                }
                if (!$document->save()) {
                    $msg = AifinHelper::modelErrorHtml("aefaef",$document->getErrors());
                    Yii::error("is it here",$msg);
                    $this->addError('app', $msg);
                    return FALSE;
                } else {
                    $this->documents[] = $document;
                }
            }
        }
        return TRUE;
    }

    /** Download DMS file / show profile picture / show DMS file as a picture
     * @param int $id
     * @param int $a
     * @param string $token
     * @return json/string/download
     * 
     * $a = 1: check file exist
     * $a = 2: download the file directly
     * $a = 4: echo file content as image
     * $a = 5: echo file content as image
     */
    public function download($id = null, $a = null, $token = null) {
        $this->dir = Yii::$app->params['dmsPath'];

        $response['success'] = false;
        $document = null;

        // security measures
        $id = intval($id);
        $encodedToken = \yii\helpers\Html::encode($token);

        if (!$a || !in_array($a, [1, 2, 4, 5])) {
            $response['msg'] = "Illegal operation logged";
            return json_encode($response);
        }

        // First search by id ONLY
        if ($id > 0) {
            $document = Document::find()
                    ->joinWith(['documentFolder'])
                    ->where(['document.id' => $id, 'document.status' => 1])
                    ->one();
        } elseif (!empty($encodedToken)) {
            $document = Document::find()
                    ->joinWith(['documentFolder'])
                    ->where(['document.token' => $encodedToken, 'document.status' => 1])
                    ->one();
        }

        if (!$document && $a == 1) {
            return FALSE;
        }


        switch ($a) {
            case 1:
            case 2:
            case 5:
                /** @var Document $results */
                if ($document->token <> $token) {
                    $response['msg'] = "Unauthorized access logged";

                    if (YII_DEBUG) {
                        
                        $response['web_token'] = $token;
                        $response['dms_token'] = $document->token;
                    }
                    echo json_encode($response);
                    exit();
                }

                if ($document->file_name) {
                    $YourFile = $this->dir . $document->file_name;

                    // respect fs_folder_name
                    if (isset($document->documentFolder->fs_folder_name)) {
                        if (!empty($document->documentFolder->fs_folder_name)) {
                            $YourFile = $this->dir . $document->documentFolder->fs_folder_name . "/" . $document->file_name;
                        }
                    }
                    if (is_file($YourFile)) {
                        if ($a == 2) {
                            $decryptedData = file_get_contents($YourFile);
                            /* use XMcrypt to decrypt the data */
//                            $decryptedData = XMcrypt::decrypt($YourFile);

                            $quoted = sprintf('"%s"', addcslashes(basename($document->orig_file_name), '"\\'));

//                            header('Content-Description: File Transfer');
//                            header('Content-Type: application/octet-stream');
                            header('Content-Type: ' . $document->file_type);

//                            header('Content-Disposition: attachment; filename=' . $quoted);
                            header('Content-Transfer-Encoding: binary');
                            header('Connection: Keep-Alive');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            header('Content-Length: ' . $document->file_size);
//                                header("Content-disposition: attachment; filename=$document->orig_file_name");
                            echo $decryptedData;
                            exit();
                        } elseif ($a == 5) {
//var_dump($YourFile); die();
                            $decryptedData = file_get_contents($YourFile);
                            /* use XMcrypt to decrypt the data */
//                            $decryptedData = XMcrypt::decrypt($YourFile);

                            $quoted = sprintf('"%s"', addcslashes(basename($document->orig_file_name), '"\\'));

                            header('Content-type: image/jpeg');
                            header('Content-Description: File Transfer');
                            header('Content-Transfer-Encoding: binary');
                            header('Connection: Keep-Alive');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            header('Content-Length: ' . $document->file_size);
                            echo $decryptedData;
                            exit();
                        }
                    } else {
                        if ($a == 1) {
                            return FALSE;
                        }
                        // Show file not found image
                        $fileNotFoundImage = "data:image/png;base64,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";
                        $codeBase64 = str_replace('data:image/png;base64,', '', $fileNotFoundImage);
                        header('Content-type: image/png');
                        echo base64_decode($codeBase64);
                        return true;
                    }
                }
                break;
//            case 3:
//                if (!$filename) {
//                    \common\helpers\ApiHandler::failure();
//                }
//                $decryptedData = file_get_contents($this->dir . "profilepic/" . $filename);
//                header('Content-Transfer-Encoding: binary');
//                echo $decryptedData;
//                break;
            case 4:
                if (!$filename) {
                    \common\helpers\ApiHandler::failure();
                }
                $decryptedData = file_get_contents($this->dir . "post_images/" . $filename);
                header('Content-Transfer-Encoding: binary');
                echo $decryptedData;
                break;
            default:
                $response['msg'] = "Illegal operation logged";
        }

        return json_encode($response);
    }

    /**
     * Process "files" in POST enviroment
     * @param type $idfolder target DocumentFolder ID
     * @param type $params iddoc(iddocument), idplan,idcr,idca,idpeople
     * @return boolean
     */
    public function processPostFiles($idfolder, $params = null) {
        if (Yii::$app->request->isPost) {

            if (isset($params['iddoc']))
                $iddoc = $params['iddoc'];
            if (isset($params['idplan']))
                $idplan = $params['idplan'];
            if (isset($params['idcr'])) // crmRecord ID
                $idcr = $params['idcr'];
            if (isset($params['idca'])) // crmActivity ID
                $idca = $params['idca'];
            if (isset($params['idpeople']))
                $idpeople = $params['idpeople'];

            $folder = DocumentFolder::findOne($idfolder);

            if (!$folder) {
                Yii::error("idfolder $idfolder not found");
                return false;
            }

            if (isset($_FILES['files'])) {
                if (!empty($_FILES['files']['name'][0])) {
                    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                        $file_name = $_FILES['files']['name'][$key];
                        $file_size = $_FILES['files']['size'][$key];
                        $file_tmp = $_FILES['files']['tmp_name'][$key];
                        $file_type = $_FILES['files']['type'][$key];

                        if ($file_size > (AifinHelper::file_upload_max_size("byte"))) {
                            $response['msg'] = 'File size must be less than ' . AifinHelper::file_upload_max_size("kb") . 'kb';
                            echo json_encode($response);
                            exit();
                        }

                        if (is_dir($this->dir) == false) {
                            mkdir($this->dir, 0775);  // Create directory if it does not exist
                        }

                        $random_file_name = AifinHelper::genFileName();

                        /*
                         * Get the fs_folder_name from DocumentFolder
                         */
                        $fsFolderName = DocumentFolder::find()->select("fs_folder_name")->where(['id' => $idfolder, 'status' => 1])->scalar();

                        if (!empty($fsFolderName)) {
                            if (is_dir($this->dir . "/" . $fsFolderName) == false) {
                                mkdir($this->dir . "/" . $fsFolderName, 0775);  // Create sub directory if it does not exist
                            }

                            $destFile = $this->dir . "/" . $fsFolderName . "/" . $random_file_name;
                        } else {
                            $destFile = $this->dir . $random_file_name;
                        }

                        if (is_dir($destFile) == false) {
                            move_uploaded_file($file_tmp, $destFile);
                            /* encrypt the file */
                            XMcrypt::encrypt($destFile);
                        } else {         //rename the file if another one exist
                            $new_dir = $this->dir . "/" . $fsFolderName . "/" . $random_file_name;

                            rename($file_tmp, $new_dir);
                        }

                        if (isset($iddoc)) {
                            $document = Document::findOne($iddoc);
                        } else {
                            $document = new Document;
                            $document->idfolder = $idfolder;
                            $document->status = 1;
                        }
                        $document->file_name = $random_file_name;
                        $document->orig_file_name = $file_name;
                        $document->file_size = (string) $file_size;
                        $document->file_type = $file_type;
                        $document->token = AifinHelper::genRandomKey(64);
                        $document->doc_desc_eng = $file_name;
                        $document->doc_desc_cht = $file_name;
                        $document->doc_desc_chs = $file_name;
                        $document->doc_desc_jap = $file_name;

                        if (isset($idplan))
                            $document->idplan = $idplan;
                        if (isset($idcr))
                            $document->idcr = $idcr;
                        if (isset($idca))
                            $document->idca = $idca;
                        if (isset($idpeople))
                            $document->idpeople = $idpeople;

                        if (!$document->save()) {
                            Yii::error("Error while saving document record");
                            return FALSE;
                        }
                    }
                    return TRUE;
                } else {
                    Yii::trace("NO FILES in POSTor file too large?");
                    return TRUE; // NO FILES in POST or file too large.....
                }
            } else {
                Yii::trace("NO FILES in POST");
                return TRUE; // NO FILES in POST
            }
        }
        return FALSE;
    }

    /**
     * Handle POST['files']
     */
    public function processDoc() {
        $response = array();
        $response['success'] = FALSE;

        if (Yii::$app->request->isGet) {
            $a = filter_input(INPUT_GET, 'a');
            $id = filter_input(INPUT_GET, 'id');
        }

        if (Yii::$app->request->isPost) {
            $a = Yii::$app->request->post('a');
            $id = Yii::$app->request->post('id');
            $idfolder = Yii::$app->request->post('idfolder');
            $doc_desc_eng = Yii::$app->request->post('doc_desc_eng');
            $doc_desc_cht = Yii::$app->request->post('doc_desc_cht') ? Yii::$app->request->post('doc_desc_cht') : Yii::$app->request->post('doc_desc_eng');
            $doc_desc_chs = Yii::$app->request->post('doc_desc_chs') ? Yii::$app->request->post('doc_desc_chs') : Yii::$app->request->post('doc_desc_eng');
            $doc_desc_jap = Yii::$app->request->post('doc_desc_jap') ? Yii::$app->request->post('doc_desc_jap') : Yii::$app->request->post('doc_desc_eng');
            $idproduct_cat = Yii::$app->request->post('idproduct_cat');
            $idprovider = Yii::$app->request->post('idprovider');
            $idproduct = Yii::$app->request->post('idproduct');

            $status = Yii::$app->request->post('status');

            // Replace existing document
            if (isset($_FILES['files'])) {

                if (!empty($_FILES['files']['name'][0])) {

                    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                        $file_name = $_FILES['files']['name'][$key];
                        $file_size = $_FILES['files']['size'][$key];
                        $file_tmp = $_FILES['files']['tmp_name'][$key];
                        $file_type = $_FILES['files']['type'][$key];

                        if ($file_size > (AifinHelper::file_upload_max_size("byte"))) {
                            $response['msg'] = 'File size must be less than ' . AifinHelper::file_upload_max_size("kb") . 'kb';
                            echo json_encode($response);
                            exit();
                        }

                        if (is_dir($this->dir) == false) {
                            mkdir($this->dir, 0775);  // Create directory if it does not exist
                        }

                        $random_file_name = AifinHelper::genFileName();

                        /*
                         * Get the fs_folder_name from DocumentFolder
                         */
                        $fsFolderName = DocumentFolder::find()->select("fs_folder_name")->where(['id' => $idfolder, 'status' => 1])->scalar();

                        if (!empty($fsFolderName)) {
                            if (is_dir($this->dir . "/" . $fsFolderName) == false) {
                                mkdir($this->dir . "/" . $fsFolderName, 0775);  // Create sub directory if it does not exist
                            }

                            $destFile = $this->dir . "/" . $fsFolderName . "/" . $random_file_name;
                        } else {
                            $destFile = $this->dir . $random_file_name;
                        }

                        if (is_dir($destFile) == false) {
                            move_uploaded_file($file_tmp, $destFile);
                            /* encrypt the file */
                            XMcrypt::encrypt($destFile);
                        } else {         //rename the file if another one exist
                            $new_dir = $this->dir . "/" . $fsFolderName . "/" . $random_file_name;

                            rename($file_tmp, $new_dir);
                        }

                        $document = Document::findOne($id);
                        $document->file_name = $random_file_name;
                        $document->orig_file_name = $file_name;
                        $document->file_size = (string) $file_size;
                        $document->file_type = $file_type;

                        if (!$document->save()) {
                            $response['success'] = false;
                            $response['msg'] = "Document Upload failed";
                            echo json_encode($response);
                            exit();
                        }
                    }
                }
            }

            // action 0:read, 1:create 2:update 3:delete
            if ($a <> 1 && $a <> 2 && $a <> 3 && $a <> 0) {
                $response['msg'] = "Invalid operation";
                echo json_encode($response);
                exit();
            }
        }

        if ($a <> 0 && $a <> 1 && $a <> 2 && $a <> 3) {
            $response['success'] = false;
            $response['errormsg'] = "Invalid Request";
            echo json_encode($response);
            exit();
        }

        switch ($a) {
            case 0:
                $data = Document::find()
                        ->select([
                            'id',
                            'idfolder',
                            'doc_desc_eng',
                            'doc_desc_cht',
                            'doc_desc_chs',
                            'doc_desc_jap',
                            'idprovider',
                            'idproduct_cat',
                            'orig_file_name',
                        ])
                        ->where(['id' => $id])
                        ->one();

                if ($data !== null) {
                    $response['success'] = true;
                    $response['data'] = $data->toArray();
                } else {
                    $response['success'] = false;
                    $response['msg'] = "Record not found";
                }
                break;
            case 1:
            case 2:
                if ($a == 1) { // NEW DOC
                    $document = new Document();
                } else {// UPDATE DOC
                    $document = Document::findOne($id);
                }

                if ($document->load(Yii::$app->request->post()) && $document->validate() && $document->save())
                    $response['success'] = true;
                else {
                    $response['msg'][] = WebLang::$t['save_failed'];
                }
                break;
            case 3:
                // Delete
                $document = Document::findOne($id);

                if ($document->softDelete()) {
                    $response['success'] = true;
                } else {
                    $response['success'] = false;
                    $response['msg'] = "Unable to delete record.";
                }
                break;
            default:
                $response['success'] = false;
                $response['msg'] = "Invalid operation";
        }
        echo json_encode($response);
    }

    public function processDocFolder($id, $a) {
        $response['success'] = FALSE;

        switch ($a) {
            case 0:
                $docFolder = DocumentFolder::find()->where(['status' => 1, 'id' => $id])->asArray()->one();
                return \common\helpers\ApiHandler::success($docFolder);
                break;
            case 1: // create
            case 2: // update
                if ($a == 1) {
                    $docFolder = new DocumentFolder();
                    $docFolder->idpeople_owner = null;
                } else {
                    $docFolder = DocumentFolder::findOne($id);
                }
                if ($docFolder->load(Yii::$app->request->post()) && $docFolder->validate()) {
                    /*
                     * Set all languages name to English if empty
                     */
                    if (empty($docFolder->folder_name_cht)) {
                        $docFolder->folder_name_cht = $docFolder->folder_name_eng;
                    }
                    if (empty($docFolder->folder_name_chs)) {
                        $docFolder->folder_name_chs = $docFolder->folder_name_eng;
                    }
                    if (empty($docFolder->folder_name_jap)) {
                        $docFolder->folder_name_jap = $docFolder->folder_name_eng;
                    }
                    /*
                     * Handle is_public if owner is set
                     */
                    if ($docFolder->idpeople_owner > 0) {
                        $docFolder->is_public = 0;
                    }
                    if ($docFolder->save()) {
                        $response['success'] = true;
                    }
                } else {
                    $response['msg'][] = WebLang::$t['save_failed'];
                }
                break;
            case 3: // Delete
                $docFolder = DocumentFolder::findOne($id);

                if (!$docFolder) {
                    $this->addError('app', 'Folder not found');
                    return FALSE;
                }
                // Delete all Documents & file
                foreach ($docFolder->documents as $document) {
                    if (!$document->delete()) {
                        return FALSE;
                    }
                    if (!$document->softDelete()) {
                        return FALSE;
                    }
                }

                if ($docFolder->softDelete()) {
                    $response['success'] = true;
                } else {
                    $response['success'] = false;
                    $response['msg'] = "Unable to delete record.";
                }
                break;
            default:
                $response['success'] = false;
                $response['msg'] = "Invalid operation";
        }

        echo json_encode($response);
        Yii::$app->end();
    }

    public function processPeopleDocFolder($id, $idpeople, $a) {

        if ($idpeople > 0) {
            $people = \common\models\People::findOne($idpeople);
        }
        if (!$idpeople) {
            return FALSE;
        }

        switch ($a) {
            case 0:
// read
                $data = DocumentFolder::findOne($id);
                if ($data !== null) {
                    $response['success'] = true;
                    $response['data'] = $data->toArray();
                } else {
                    $response['success'] = false;
                    $response['msg'] = "Record not found";
                }
                break;
            case 1: // create
            case 2: // update
                if ($a == 1) {
                    $docFolder = new DocumentFolder();
                    $docFolder->status = 1;
                    $docFolder->idpeople_owner = $idpeople;
                    $docFolder->fs_folder_name = $idpeople;
                } else {
                    $docFolder = DocumentFolder::findOne($id);
                }
                $docFolder->folder_name_eng = $idpeople . " " . WebLang::t('people', 'eng') . WebLang::sp('eng') . WebLang::t('folder', 'eng');
                $docFolder->folder_name_cht = $idpeople . " " . WebLang::t('people', 'cht') . WebLang::sp('cht') . WebLang::t('folder', 'cht');
                $docFolder->folder_name_chs = $idpeople . " " . WebLang::t('people', 'chs') . WebLang::sp('chs') . WebLang::t('folder', 'chs');
                $docFolder->folder_name_jap = $idpeople . " " . WebLang::t('people', 'jap') . WebLang::sp('jap') . WebLang::t('folder', 'jap');

                if ($docFolder->validate() && $docFolder->save()) {
                    $people->idfolder = $docFolder->id;

                    if ($people->save()) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
                break;
            case 3:
// Delete
                $docFolder = DocumentFolder::findOne($id);

                if ($docFolder->softDelete()) {
                    return true;
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }
        return false;
    }

    /**
     * Bulk delete DMS document
     * @param type $id
     * @param type $token
     */
    public function delete($id, $token) {
        $document = Document::find()
                ->joinWith(['documentFolder'])
                ->where(['document.id' => $id, 'document.token' => $token, 'document.status' => 1])
                ->one();

        if (!$document) {
            $this->addError('app', $msg = 'Unable to locate document by the data provided');
            Yii::error($msg);
            return FALSE;
        }

        if (!$document->delete()) {
            return FALSE;
        }
        return TRUE;
    }

}
