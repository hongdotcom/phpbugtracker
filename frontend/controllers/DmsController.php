<?php

namespace frontend\controllers;

use Yii;
use common\controllers\MasterController;
use common\models\DmsModel;
use common\models\Document;
use common\models\DocumentFolder;
use common\models\WebLang;

class DmsController extends MasterController {

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex() {

        $f = Yii::$app->request->get('f', 1);
        $q = Yii::$app->request->post('q', NULL);

        $error = NULL;

        $folder = DocumentFolder::findOne(1);

        if (!$folder) {
            $this->internalServerError();
        }

        $idparent = DocumentFolder::getIdParent($f);

        /*
         * Important data control
         */
        $cageCheckPass = FALSE;

        $folderHistory = [];

        /*
         * SEARCH must be POST type
         */
        if (Yii::$app->request->isPost && !empty(trim($q))) {

            $validFolder = true;

            // Build Search result
            $folderName = WebLang::t("search_result");

            $idpeople_owner = 0;
            $folders = null; // search results should return NO folder list

            $docs = DocumentFolder::search($f, $q, $peopleTree);
        } else {

            $folderName = DocumentFolder::getFolderName($f);

            // prepare folder hierarchy array
            $folderHistory[] = ['id' => $f, 'name' => $folderName];

            // Construct folder path history
            $idparentCheck = $idparent;

            while ($idparentCheck > 0) {
                $folderHistory[] = ['id' => $idparentCheck, 'name' => DocumentFolder::getFolderName($idparentCheck)];
                $idparentCheck = DocumentFolder::getIdparent($idparentCheck);
            }
            unset($idparentCheck);

            $folderHistory = array_reverse($folderHistory);
            $folderHistoryCount = count($folderHistory);

            // Folder Owner
            // * When create subfolder, use the same owner of current folder
            $idpeople_owner = DocumentFolder::getFolderOwner($f);

            if (!$idpeople_owner) {
                $idpeople_owner = 0;
            }

            if (!$folderName) {
                $folderName = "<h2 class='lead text-center'>Folder $folderName not exist</h2>";
                $validFolder = false;
            } else {
                $validFolder = true;
            }


            $folders = DocumentFolder::getAllByIdparent($f, null, Yii::$app->user->identity->id);
            $docs = Document::getAllByIdfolder($f);
        }
//var_dump($folders); die();

        return $this->render('index', [
                    'folder' => $folder,
                    'folderHistory' => $folderHistory,
                    'validFolder' => $validFolder,
                    'f' => $f,
                    'q' => (isset($q) ? $q : ""),
                    'folders' => $folders,
                    'idparent' => $idparent,
                    'docs' => $docs,
                    'error' => $error,
                    'lang' => Yii::$app->language,
        ]);
    }

    public function actionUpload() {
        $model = new DmsModel();
        return $model->upload();
    }

    public function actionDownload() {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $a = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_NUMBER_INT);
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

        $model = new DmsModel();
        return $model->download($id, $a, $token);
    }

    public function actionDelete() {
        $model = new DmsModel();
        return $model->delete();
    }

    public function actionProcessDoc() {
        $model = new DmsModel();
        return $model->processDoc();
    }

    public function actionProcessDocFolder() {
        $model = new DmsModel();
        return $model->processDocFolder();
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
