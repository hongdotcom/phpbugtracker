<?php

namespace common\models;

use Yii;
use yii\helpers\Url;
use common\models\DmsModel;

/**
 * This is the model class for table "document".
 *
 * @property integer $id
 * @property integer $idfolder
 * @property integer $idproduct_cat
 * @property integer $idcommission_schedule
 * @property integer $idplan
 * @property integer $idproduct
 * @property integer $idprovider
 * @property integer $idpeople
 * @property integer $idcr
 * @property integer $idca
 * @property string $doc_desc_eng
 * @property string $doc_desc_cht
 * @property string $doc_desc_chs
 * @property string $doc_desc_jap
 * @property string $orig_file_name
 * @property string $file_name
 * @property string $file_type
 * @property string $file_size
 * @property string $token
 * @property integer $form
 * @property integer $pi
 * @property integer $asf
 * @property integer $gi
 * @property integer $nb
 * @property integer $ui
 * @property integer $locked
 * @property integer $checked_out_idpeople
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 *
 * @property DocumentFolder $idfolder0
 */
class Document extends \yii\db\ActiveRecord {

    public $whitelist = array(
        "id",
        "idfolder",
        "idcommission_schedule",
        "idproduct",
        "idproduct_cat",
        "idplan",
        "idprovider",
        "idpeople",
        "idcr",
        "idca",
        "doc_desc_eng",
        "doc_desc_cht",
        "doc_desc_chs",
        "doc_desc_jap",
        "orig_file_name",
        "file_name",
        "file_type",
        "file_size",
        "token",
        "locked",
        "checked_out_idpeople",
        "status"
    );

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'document';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['orig_file_name', 'file_name', 'file_type', 'file_size'], 'required'],
            [['idfolder', 'idproduct_cat', 'idcommission_schedule', 'idplan', 'idproduct', 'idprovider', 'idpeople',
            'idcr', 'idca', 'locked', 'checked_out_idpeople', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'id'], 'safe'],
            [['doc_desc_eng', 'doc_desc_cht', 'doc_desc_chs', 'doc_desc_jap', 'orig_file_name',
            'file_name', 'file_type', 'file_size'], 'string', 'max' => 255],
            [['token'], 'string', 'max' => 64],
            [['token'], 'unique'],
            [['idfolder'], 'exist', 'skipOnError' => true, 'targetClass' => DocumentFolder::className(), 'targetAttribute' => ['idfolder' => 'id']],
            [['doc_desc_eng', 'doc_desc_cht', 'doc_desc_chs', 'doc_desc_jap'], 'default', 'value' => NULL]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentFolder() {
        return $this->hasOne(DocumentFolder::className(), ['id' => 'idfolder']);
    }

    public function getCommStatementSchedule() {
        return $this->hasOne(CommStatementSchedule::className(), ['id' => 'idcommission_schedule']);
    }

    /**
     * 
     * @param type $text
     * @param type $authorisedIdfolders
     * @return type
     */
    public function search($searchString, $authorisedIdfolders = null, $peopleTree = null) {
        $text = \common\helpers\IfapHelper::StringInputCleaner($searchString);

        $query = self::find()
                ->select($this->whitelist)
                ->where(['document.status' => 1])
                ->andWhere("file_name is not null AND file_name <> ''");

        $query->andFilterWhere([
            'OR',
            ["LIKE", "doc_desc_eng", $text],
            ["LIKE", "doc_desc_cht", $text],
            ["LIKE", "doc_desc_chs", $text],
            ["LIKE", "doc_desc_jap", $text],
            ["LIKE", "orig_file_name", $text],
        ]);

        if ($authorisedIdfolders) {
            $query = $query->andWhere(['in', 'idfolder', $authorisedIdfolders]);
        }

        return $query->all();
    }

    public static function getAllByIdFolder($idfolder) {
        $query = self::find()
                ->joinWith(['documentFolder'])
                ->andWhere([
                    'document.idfolder' => $idfolder
                ])
                ->andWhere(['document.status' => 1]);

        $docs = $query->all();
        return $docs;
    }

    public static function getAllByFolders($arrIdfolder, $limit = null, $params = null) {
        $query = self::find()
                ->joinWith(['documentFolder'])
                ->andWhere([
                    'in', 'idfolder', $arrIdfolder
                ])
                ->andWhere(['document.status' => 1, 'document_folder.status' => 1])
                ->orderBy(['document.created_at' => 'desc']);

        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $query = $query->andWhere([$key => $value]);
            }
        }
        if ($limit > 0) {
            return $query->limit($limit)->all();
        } else {
            return $query->all();
        }
    }

    public function name() {
        $lang = Yii::$app->language;

        if (!empty($name = $this->{"doc_desc_$lang"})) {
            return $name;
        }
        return $this->doc_desc_eng;
    }

    /**
     * Delete document table row
     * Delete file system data
     */
    public function delete() {
        $dir = Yii::$app->params['crmPath'];

        if (isset($this->documentFolder->fs_folder_name)) {
            $fsFolderName = $this->documentFolder->fs_folder_name;

            if (!empty($fsFolderName)) {
                $destFile = $dir . "/" . $fsFolderName . "" . $this->file_name;
            }
        } else {
            // the file should be at DMS BASE folder
            $destFile = $dir . "/" . $this->file_name;
        }
//        Yii::error("Trying to delete $destFile");

        /*
         * Try to delete the file from system, don't throw error if it is no found!
         */
        if (is_file($destFile)) {
            $hap = unlink($destFile);

            if (!$hap) {
                Yii::error($msg = "Unable to unlink $destFile");
            } else {
                Yii::error("Unlinked $destFile");
            }
        } else {
            Yii::error($msg = "Unable to locate file in path $destFile");
        }

        if (!$this->softDelete()) {
            throw new \Exception("softdelete error");
        }
        return TRUE;
    }

    public function checkFileExist() {
        $dmsModel = new DmsModel;
        return $dmsModel->download($this->id, 1, $this->token);
    }

    public function getFiletypeIcon() {

        $urlPrefix = "<img src='" . Url::to(['/']) . "images/filetypes/";
        $urlSuffix = "' class='dms-preview'>";
        $filename = "unknown.jpg";

        if (strstr($this->orig_file_name, ".jpg") || strstr($this->orig_file_name, ".png") || strstr($this->orig_file_name, ".gif") || strstr($this->orig_file_name, ".jpeg")) {
            return "<img class='dms-preview' src='" . Url::to(['/dms/download']) . "?a=5&id=" . $this->id . "&token=" . $this->token . "'>";
        }

        if (strstr($this->orig_file_name, ".pdf")) {
            $filename = "pdf.jpg";
        }
        if (strstr($this->orig_file_name, ".zip")) {
            $filename = "zip.jpg";
        }
        if (strstr($this->orig_file_name, ".xls") || strstr($this->orig_file_name, ".xlsx")) {
            $filename = "xls.jpg";
        }
        if (strstr($this->orig_file_name, ".doc") || strstr($this->orig_file_name, ".docx")) {
            $filename = "word.jpg";
        }
        if (strstr($this->orig_file_name, ".ppt") || strstr($this->orig_file_name, ".pptx")) {
            $filename = "ppt.jpg";
        }
        return $urlPrefix . $filename . $urlSuffix;
    }

    public function getCheckedOutPeople() {
        return $this->hasOne(People::className(), ['id' => 'checked_out_idpeople'])
                        ->onCondition([People::tableName() . '.status' => 1]);
    }

}
