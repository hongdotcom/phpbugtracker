<?php

namespace backend\controllers;

use common\models\DmsModel;
use backend\models\ImportExcel;
use common\controllers\MasterController;
use common\helpers\IfapHelper;
use common\models\Document;
use common\models\DocumentFolder;
use common\models\People;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Provider;
use Yii;
use yii\helpers\Json;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use common\helpers\ApiHandler;

class DmsController extends MasterController {

    public function actionIndex() {

        $srcIdFolder = null;
        $error = NULL;

        $idfolder = Yii::$app->request->get('f'); // target folder
        $src = Yii::$app->request->get('src'); // parent folder requested by user
        $token = Yii::$app->request->get('token');
        $q = Yii::$app->request->post('q', NULL); // search string

        $folder = null;

        if ($idfolder > 0) {
            $folder = DocumentFolder::findActiveOne($idfolder);

            if (!$folder) {
                $this->internalServerError();
            }
        }

        $docs = null;
        $folderHistory = null;
        $idpeople_owner = null;
        $idparent = null;
        $folderCanBeDeleted = FALSE;

        if ($folder) {
            $idparent = $folder->idparent;

            if (!$folder->is_public) {
                // verify token if is_public=null
                if (!$token) {
                    $this->unauthorisedError();
                }
                if (!IfapHelper::verifySingingToken(['id' => (int) $idfolder], $token)) {
                    $this->unauthorisedError();
                }
            }

            if ($folder->id <> $srcIdFolder && $src <> $srcIdFolder) {
                // Validate targetFolder is a subling of srcFolder
                $idparent = DocumentFolder::getIdParent($idfolder);

                while ($idparent && $idparent > 0) {
                    $idparent = DocumentFolder::getIdparent($idparent);

                    if ($idparent == $srcIdFolder)             // validated
                        break;
                }
            }

            $folderHistory = [];

            if (Yii::$app->request->isPost && !empty(trim($q))) {
                $validFolder = true;
                $idparent = $srcIdFolder;

                // Build Search result
                $folderName = "Search Result";
                $docs = DocumentFolder::search($idfolder, $q);
                $idpeople_owner = 0;
                $folders = null;
            } else {

                $folderName = DocumentFolder::getFolderName($folder->id);
                $idparent = $folder->idparent;

                // prepare folder hierarchy array
                $folderHistory[] = ['id' => $idfolder, 'name' => $folderName];

                // Seek the top level idparent
                $idparentTop = $folder->idparent;

                while ($idparentTop > 0) {
                    $folderHistory[] = ['id' => $idparentTop, 'name' => DocumentFolder::getFolderName($idparentTop)];
                    $idparentTop = DocumentFolder::getIdparent($idparentTop);
                }
                $folderHistory = array_reverse($folderHistory);
                $folderHistoryCount = count($folderHistory);

                // Folder Owner
                // * When create subfolder, use the same owner of current folder
                $idpeople_owner = DocumentFolder::getFolderOwner($idfolder);

                if (!$idpeople_owner) {
                    $idpeople_owner = 0;
                }
                // No need data control backend, ignore idpeople_owner, is_public, tree_access
                $folders = DocumentFolder::find()
                        ->where([
                            'system_folder' => 0,
                            'status' => 1,
//                            "idpeople_owner" => 0
                        ])
                        ->andWhere(["idparent" => $folder->id])
                        ->all();

                $docs = Document::getAllByIdfolder($folder->id);
            }
        } else {
            // Should be standing at DMS ROOT here
            // No need data control backend?
            $folders = DocumentFolder::find()
                    ->where([
                        'system_folder' => 0,
                        'status' => 1,
                        "idpeople_owner" => 0,
                        "idparent" => 0
                    ])
                    ->all();
        }

        $allConsultants = People::getAllWithinLevel(2, 10); // exclude Desk Head

        if ($folder) {
            if (!in_array($folder->id, [DocumentFolder::FRONTEND_CLIENT, DocumentFolder::FRONTEND_CONSULTANT])) {
                $folderCanBeDeleted = TRUE;
            }
        }

        return $this->render('index', [
                    'folder' => $folder,
                    'folderHistory' => $folderHistory,
                    'folderCanBeDeleted' => $folderCanBeDeleted,
                    'srcFolder' => $srcIdFolder,
                    'f' => $idfolder,
                    'idpeople_owner' => $idpeople_owner,
                    'folders' => $folders,
                    'idparent' => $idparent,
                    'docs' => $docs,
                    'providers' => ArrayHelper::map(Provider::getList(), "id", "itemname"),
                    'productCats' => ArrayHelper::map(ProductCategory::getList(), "id", "itemname"),
                    'products' => ArrayHelper::map(Product::getList(), "id", "itemname"),
                    'allConsultants' => $allConsultants,
                    'error' => $error,
                    'q' => $q,
        ]);
    }

    public function actionUpload() {
        if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) &&
                strtolower($_SERVER['REQUEST_METHOD']) == 'post') { //catch file overload error...
            ApiHandler::error500("Invalid method detected");
        }
        if (!isset($_FILES['files']) && !isset($_FILES['file'])) {
            ApiHandler::error500("Please select a valid file (" . IfapHelper::file_upload_max_size("kb") . "kb maximum)");
        }

        $model = new DmsModel();
        if (!$model->upload()) { // upload() handles ['file'], NOT ['files']
            $msg = IfapHelper::modelErrorHtml($model->getErrors(), FALSE);
            ApiHandler::error500($msg);
        }
        ApiHandler::custom([
            'success' => true,
            'iddoc' => ArrayHelper::map($model->documents, "id", "id")
        ]);
    }

    /*
     * This shud be a public accessible url (e.g. frontend)
     */

    public function actionDownload() {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $a = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_NUMBER_INT);
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

        $model = new DmsModel();
        return $model->download($id, $a, $token);

//        if (isset($jsonResponse['success']) || !$jsonResponse) {
//            if ($jsonResponse['success'] <> true) {
//                $this->notFoundError();
//            }
//        }
//        return $jsonResponse;
    }

    public function actionDelete() {
        $id = Yii::$app->request->post('id');
        $token = Yii::$app->request->post('token');

        if (!$id || !$token) {
            ApiHandler::failure("Invalid call detected");
        }

        $document = Document::find()
                ->joinWith(['documentFolder'])
                ->where(['document.id' => $id, 'document.status' => 1])
                ->one();

        if (!$document) {
            ApiHandler::failure();
        }
        if ($document->token <> $token) {
            ApiHandler::failure("token not match");
        }

        if ($document->delete()) {
            ApiHandler::success();
        } else {
            ApiHandler::failure();
        }
    }

    public function actionProcessDoc() {
        $model = new DmsModel();
        return $model->processDoc();
    }

    public function actionProcessDocFolder() {
        $id = Yii::$app->request->post('id');
        $a = Yii::$app->request->post('a');

        // action 0:read, 1:create 2:update 3:delete
        if (!in_array($a, [1, 2, 3, 0])) {
            ApiHandler::failure("Invalid operation");
        }
        $model = new DmsModel();

//        $transaction = Yii::$app->db->beginTransaction();

        if ($model->processDocFolder($id, $a)) {
//            $transaction->commit();
            ApiHandler::success();
        }
//        $transaction->rollBack();
        ApiHandler::failure(IfapHelper::modelErrorHtml($model->getErrors()));
    }

    public function actionProcessPeopleDocFolder() {

        $id = Yii::$app->request->post('id');
        $idpeople = Yii::$app->request->post('idpeople');
        $a = Yii::$app->request->post('a');

        // action 0:read, 1:create 2:update 3:delete
        if (!in_array($a, [1, 2, 3, 0])) {
            ApiHandler::failure("Invalid operation");
        }
        switch ($a) {
            case 0:
                // read
                if ($id > 0) {
                    return DocumentFolder::findActiveOne($id)->toArray();
                }
                ApiHandler::failure();
                break;
            case 1:
            case 2:
            case 3:
                $model = new DmsModel();
                if ($model->processPeopleDocFolder($id, $idpeople, $a)) {
                    ApiHandler::success();
                } else {
                    ApiHandler::failure();
                }
                break;
        }
        ApiHandler::failure();
    }

    public function actionUploadExcel() {
        $fileName = 'file';

        if (isset($_FILES[$fileName])) {
            $uploadModel = new ImportExcel();
            $uploadModel->excel = UploadedFile::getInstanceByName($fileName);

            if ($uploadModel->upload())
                return Json::encode($uploadModel->getFilePath());
        }
        return false;
    }

    public function actionProcessBulkAction() {

//        $this->isAjax();

        $a = Yii::$app->request->post('a');
        $iddoc = Yii::$app->request->post('iddoc');
        $token = Yii::$app->request->post('token');
        $idfolder = Yii::$app->request->post('idfolder');
        $idproduct_cat = Yii::$app->request->post('idproduct_cat');
        $idprovider = Yii::$app->request->post('idprovider');
        $idproduct = Yii::$app->request->post('idproduct');
        $targetIdfolder = Yii::$app->request->post('target_folder');

        switch ($a) {
            case 1: // lock
                foreach ($iddoc as $id) {

                    $Document = Document::findActiveOne($id);
                    if (!$Document) {
                        ApiHandler::failure();
                    }

                    $Document->locked = 1;
                    if (!$Document->save()) {
                        ApiHandler::failure();
                        break;
                    }
                }
                echo ApiHandler::success();
                break;
            case 2: // update
                foreach ($iddoc as $id) {

                    if ($id > 0) {
                        $Document = Document::findActiveOne($id);
                    } else {
                        $Document = new Document;
                    }

                    $Document->idfolder = $idfolder;
                    $Document->idproduct_cat = $idproduct_cat;
                    $Document->idprovider = $idprovider;
                    $Document->idproduct = $idproduct;

                    if (!$Document->save()) {
                        // Has errror
                        ApiHandler::failure();
                        break;
                    }
                }
                echo ApiHandler::success();
                break;
            case 3: // delete
                if (count($iddoc) > 0) {
                    $dmsModel = new DmsModel();

                    foreach ($iddoc as $index => $id) {
                        $result = $dmsModel->delete($id, $token[$index]);

                        if (!$result) {
                            ApiHandler::failure();
                        }
                    }
                    ApiHandler::success();
                }
                ApiHandler::failure();
                break;
            case 4: // check in
                foreach ($iddoc as $id) {

                    $Document = Document::findActiveOne($id);
                    if (!$Document) {
                        ApiHandler::failure();
                    }

                    $Document->checked_out_idpeople = Yii::$app->user->identity->idpeople;

                    if (!$Document->save()) {
                        ApiHandler::failure();
                        break;
                    }
                }
                echo ApiHandler::success();
                break;
            case 5: // unlock
                foreach ($iddoc as $id) {

                    $Document = Document::findActiveOne($id);
                    if (!$Document) {
                        ApiHandler::failure();
                    }

                    $Document->locked = null;
                    if (!$Document->save()) {
                        ApiHandler::failure();
                        break;
                    }
                }
                echo ApiHandler::success();
                break;
            case 6: // check out
                foreach ($iddoc as $id) {

                    $Document = Document::findActiveOne($id);
                    if (!$Document) {
                        ApiHandler::failure();
                    }

                    $Document->checked_out_idpeople = null;

                    if (!$Document->save()) {
                        ApiHandler::failure();
                        break;
                    }
                }
                ApiHandler::success();
                break;
            case 7: // move
                // Make sure targetIdFolder exists
                if (!DocumentFolder::findActiveOne($targetIdfolder)) {
                    ApiHandler::failure("Target folder not found");
                }

                foreach ($iddoc as $id) {
                    $document = Document::findActiveOne($id);

                    if (!$document) {
                        ApiHandler::failure("Invalid Document");
                    }

                    $document->idfolder = $targetIdfolder;

                    if (!$document->save()) {
                        // Has errror
                        ApiHandler::failure();
                        break;
                    }
                }
                echo ApiHandler::success();
                break;
            default:
                break;
        }
        echo ApiHandler::failure("Invalid action logged");
    }

    public function actionLockDoc() {
        $id = Yii::$app->request->post('id');
        $token = Yii::$app->request->post('token');

        if (!$id || !$token) {
            ApiHandler::failure("Invalid call detected");
        }

        $document = Document::find()
                ->joinWith(['documentFolder'])
                ->where(['document.id' => $id, 'document.status' => 1, 'token' => $token])
                ->one();

        if (!$document) {
            ApiHandler::failure();
        }

        if ($document->locked == 1) {
            $document->locked = null;
        } else {
            $document->locked = 1;
        }
        if (!$document->save()) {
            ApiHandler::failure();
        }
        ApiHandler::success();
    }

    public function actionCheckoutDoc() {
        $id = Yii::$app->request->post('id');
        $token = Yii::$app->request->post('token');

        if (!$id || !$token) {
            ApiHandler::failure("Invalid call detected");
        }

        $document = Document::find()
                ->joinWith(['documentFolder'])
                ->where(['document.id' => $id, 'document.status' => 1, 'token' => $token])
                ->one();

        if (!$document) {
            ApiHandler::failure();
        }

        if ($document->checked_out_idpeople > 0) {
            $document->checked_out_idpeople = null;
        } else {
            $document->checked_out_idpeople = Yii::$app->user->identity->idpeople;
        }
        if (!$document->save()) {
            ApiHandler::failure();
        }
        ApiHandler::success();
    }

    public function actionSync($id) {
//        if(!Yii::$app->request->isPost) {
//            $this->internalServerError();
//        }
        $apiKey = Yii::$app->request->post('apikey');
        $token = Yii::$app->request->post('token');

        if (!$doc = Document::findActiveOne($id)) {
            $this->internalServerError();
        }

        if (!$doc->checkFileExist()) {
            $this->notFoundError();
        }

        $dmsModel = new DmsModel();
        echo $dmsModel->download($id, 2, $doc->token);
    }

    public function actionDownloadPdf($file) {
        $pdf = file_get_contents(Yii::$app->params['dmsPath'] . 'pdf/' . $file);
        header('Content-type: application/pdf');
        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo $pdf;
    }

}
