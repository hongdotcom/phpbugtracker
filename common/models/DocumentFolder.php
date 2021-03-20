<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "document_folder".
 *
 * @property integer $id
 * @property string $folder_name_eng
 * @property string $folder_name_cht
 * @property string $folder_name_chs
 * @property string $folder_name_jap
 * @property integer $idparent
 * @property integer $is_public
 * @property string $tags
 * @property integer $idpeople_owner
 * @property integer $tree_access
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 * @property integer $status
 */
class DocumentFolder extends \yii\db\ActiveRecord {

    const BBS = 1;
    const FRONTEND_CLIENT = 2;
    const FRONTEND_CONSULTANT = 3;
    const PLAN = 4;
    const PRODUCT = 6;
    const CRM = 7;
    const PUBLIC_ONLY = 1;
    const OWNER_ONLY = 2;
    const PUBLIC_AND_OWNED = 3;
    const PUBLIC_AND_OWNED_AND_TREEACCESS = 4;
    const WHATEVER_PERMITTED = 5;
    const EXCLUDE_OWNED = 6;

    public static $whitelist = [
        "idparent",
        "id",
        "is_public",
        "folder_name_eng",
        "folder_name_cht",
        "folder_name_chs",
        "folder_name_jap",
        "tags",
        "idpeople_owner",
        "tree_access",
        "created_at",
        "status"
    ];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'document_folder';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            //[['idparent'], 'required'],
            [['idparent', 'idpeople_owner', 'tree_access', 'is_public', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['folder_name_eng', 'folder_name_cht', 'folder_name_chs', 'folder_name_jap', 'tags'], 'string', 'max' => 255],
            [['folder_name_eng', 'folder_name_cht', 'folder_name_chs', 'folder_name_jap', 'tags'], 'trim'],
            [['status'], 'default', 'value' => 1],
            [['content'], 'string'],
            [['folder_name_eng', 'folder_name_cht', 'folder_name_chs', 'folder_name_jap', 'tags'], 'default', 'value' => NULL],
            [['idpeople_owner'], 'required', 'when' => function ($model) {
                    return $model->tree_access == 1;
                }
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments() {
        return $this->hasMany(Document::className(), ['idfolder' => 'id'])
                        ->onCondition([Document::tableName() . '.status' => 1]);
    }

    public static function getIdParent($id) {
        return self::find()
                        ->select(['idparent'])
                        ->where([
                            'id' => $id,
                            'status' => 1
                        ])
                        ->scalar();
    }

    public static function getFolderName($id) {
        return self::find()
                        ->select(["folder_name_" . Yii::$app->language])
                        ->where([
                            'id' => $id,
                            'status' => 1
                        ])
                        ->scalar();
    }

    public static function getFolderOwner($idfolder) {
        return self::find()
                        ->select(["idpeople_owner"])
                        ->where([
                            'id' => $idfolder,
                            'status' => 1
                        ])
                        ->scalar();
    }

    public function getSubfolders() {
        return $this->find()
                        ->where([
                            'idparent' => $this->id,
                            'status' => 1
                        ])
                        ->all();
    }

    /**
     * 2018-3-8 NEW LOGIC: added new column "tree_access". if tree_access=1, idpeople_owner & ALL downlines can access the folder
     * 2018-7-10 ISSUE: incoming $arrIdpeople does not include UPLINE idpeoples!
     * @param int $idfolder
     * @param array $arrIdpeople A simple array of idpeople for SELECT IN clause
     * @param int $idpeople target idpeople to check for tree_access=1
     * @return object
     */
    public static function getAllByIdparent($idfolder, $arrIdpeople = null, $idpeople = null) {

        $query = self::find()
                ->select(self::$whitelist)
                ->where([
            'idparent' => $idfolder,
            'status' => 1
        ]);

        $queryClause = "";

        if ($arrIdpeople) {
            // DONT append to query yet
            $queryClause = "(idpeople_owner IN (" . implode(',', $arrIdpeople) . ") AND idparent > 0)";
        }

        if (!empty($queryClause)) {
            $query->andWhere($queryClause);
        }
        return $query->all();
    }

    /**
     * Get DIRECT next level idfolders
     * @param type $idfolder
     * @param type $arrIdpeople
     * @return array of folder id
     */
    public static function getAllFoldersByIdparent($idfolder, $arrIdpeople = null) {

        $query = self::find()
                ->select('id')
                ->where([
            'status' => 1
        ]);

        if ($arrIdpeople) {
            // DONT append to query yet
            $queryClause = "(idpeople_owner IN (" . implode(',', $arrIdpeople) . ")";
            $query->andWhere("$queryClause OR is_public=1) AND idparent <> 0");
        } else {
            // if no arrIdpeople provide, search ALL Folders
            $query->andWhere("idparent <> 0");
        }

        // Commented on 2018-4-13 since frontend cannot search some public folders
//        $arrIdfolders = self::getIdDescendants($idfolder);
//        if ($arrIdfolders) {
//            $query->andWhere(['in', 'id', $arrIdfolders]);
//        }

        $results = ArrayHelper::getColumn($query->asArray()->all(), 'id');

        foreach ($results as $i => $result) {
            // Allow ONLY document searching under SAME USER Role DMS root (Client / Consultant / Internal User)
            $valid = FALSE;
            $documentRootFolder = DocumentFolder::getRootParent($result);

            if (!$documentRootFolder) {
                // root folder will not have another root folder! e.g. passing idfolder=2 here
//                dd($idfolder);
                return [$idfolder];
            }

            if (Yii::$app->user->identity->idpeople_role_type == PeopleRoleType::CLIENT) {
                if ($documentRootFolder->id == DocumentFolder::FRONTEND_CLIENT) {
                    $valid = TRUE;
                }
            }
            if (Yii::$app->user->identity->idpeople_role_type >= 1 && Yii::$app->user->identity->idpeople_role_type <= 10) { // AGENT1 to 10
                if ($documentRootFolder->id == DocumentFolder::FRONTEND_CONSULTANT) {
                    $valid = TRUE;
                }
            }
            // Internal user allows to access all DocumentFolders
            if (Yii::$app->user->identity->idpeople_role_type == PeopleRoleType::STAFF) {
                $valid = TRUE;
            }

            if (!$valid) {
                unset($results[$i]);
            }
        }

        if ($results) {
            return array_merge([$idfolder], $results);
        } else {
            return [$idfolder];
        }
    }

    /**
     * 
     * @param int $f idfolder
     * @param string $text
     * @param array $peopleTree optional
     * @return type
     */
    public static function search($f, $text, $peopleTree = null) {
        $document = new Document();

        $authorisedIdfolders = self::getAllFoldersByIdparent($f, $peopleTree);

        return $document->search($text, $authorisedIdfolders, $peopleTree);
    }

    public function name() {
        if (!empty($name = $this->{'folder_name_' . Yii::$app->language})) {
            return $name;
        }
        $this->folder_name_eng;
    }

    /**
     * Get descending folders in ALL downline levels
     * @param integer $idfolder
     * @return array simple array of idfolder
     */
    public static function getIdDescendants($idfolder, $type = null) {

        Yii::trace("getIdDescendants starting from root folder $idfolder");

        $rootLevelArray[] = $idfolder;
        $nextLevelDescentands = $rootLevelArray;
        $descendants[] = $idfolder;

        while (true) {
            if (count($nextLevelDescentands) > 0) {

                $query = self::find()
                        ->select('id')
                        ->andWhere(['in', 'idparent', $nextLevelDescentands])
                        ->andWhere(['status' => 1]);

                if (!$type) {
                    $type = self::PUBLIC_ONLY;
                }
                switch ($type) {
                    case self::PUBLIC_ONLY:
                    case self::EXCLUDE_OWNED:
                        $query->andWhere(['idpeople_owner' => null]);
                        break;
                }
                $results = $query->all();

                $nextLevelDescentands = [];

                if (count($results) > 0) {
                    foreach ($results as $result) {
                        $descendants[] = $result->id;
                        $nextLevelDescentands[] = $result->id;
                    }
                } else {
                    break;
                }
            } else {
                break;
            }
        }
//        return array_merge($rootLevelArray, $descendants);
        return $descendants;
    }

    public static function getRootParent($idfolder) {

        $currentIdfolder = $idfolder;

        while (TRUE) {
            $model = self::findActiveOne($currentIdfolder);

            if (!$model) {
                break;
            }
            if (!$model->idparent) {
                break;
            }
            $currentIdfolder = $model->idparent;
        }

        return $model;
    }

    public function getOwner() {
        return $this->hasOne(People::className(), ['id' => 'idpeople_owner'])
                        ->onCondition([People::tableName() . '.status' => 1]);
    }

    public function getParentFolder() {
        return $this->hasOne(DocumentFolder::className(), ['id' => 'idparent'])
                        ->onCondition([DocumentFolder::tableName() . '.status' => 1]);
    }

}
