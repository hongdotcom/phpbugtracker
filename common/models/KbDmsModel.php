<?php

namespace common\models;

use common\helpers\IfapHelper;
use common\helpers\XMcrypt;
use common\models\KbDocument;
use yii\base\Model;
use Yii;

class KbDmsModel extends Model {

    public $dir;
    public $documents; // array of iddocument

    public function init() {
        $this->dir = Yii::$app->params['dmsPath'] . "kb/";
    }

    /**
     * This function process the POST variables for data
     * @param type $targetFolder
     * @return boolean
     */
    public function upload() {

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
                if ($file_size > IfapHelper::file_upload_max_size("byte")) {
                    $errors[] = 'File size must be less than ' . IfapHelper::file_upload_max_size("kb") . 'kb';
                }
                if (is_dir($this->dir) == false) {
                    mkdir($this->dir, 0775);  // Create directory if it does not exist
                }

                $random_file_name = IfapHelper::genUuid();

                $destFile = $this->dir . $random_file_name;

                if (is_dir($destFile) == false) {
                    move_uploaded_file($file_tmp, $destFile);

                    /* encrypt the file */
                    XMcrypt::encrypt($destFile);
                } else {         //rename the file if another one exist
                    $new_dir = $destFile;
                    rename($file_tmp, $new_dir);
                }

                $document = new KbDocument();
                $document->file_name = $random_file_name;
                $document->orig_file_name = $file_name;
                $document->file_size = (string) $file_size;
                $document->file_type = $file_type;
                $document->token = IfapHelper::genRandomKey(64);

                if (!$document->save()) {
                    $msg = IfapHelper::modelErrorHtml($document->getErrors());
                    Yii::error($msg);
                    $this->addError('app', $msg);
                    return FALSE;
                } else {
                    $this->documents[] = $document;
                }
            }
        }
        return TRUE;
    }

    /** Download KbDMS file
     * @param string $token
     * @return json/string/download
     */
    public function download($token) {

        $document = null;
        $response['success'] = false;

        if (!$token) {
            $response['msg'] = "Illegal operation logged";
            $response['errorcode'] = 1;
            return json_encode($response);
        }

        $encodedToken = \yii\helpers\Html::encode($token);

        if (!empty($encodedToken)) {
            $document = KbDocument::find()
                    ->where(['token' => $encodedToken, 'status' => 1])
                    ->one();
        }

        if (!$document) {
            $response['msg'] = "File not found";
            $response['errorcode'] = 2;
            return json_encode($response);
        }

        $YourFile = $this->dir . $document->file_name;

        if (is_file($YourFile)) {
            /* use XMcrypt to decrypt the data */
            $decryptedData = XMcrypt::decrypt($YourFile);

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
        } else {
            return FALSE;
        }
    }

}
