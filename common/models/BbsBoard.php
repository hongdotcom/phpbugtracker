<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bbs_board".
 *
 * @property integer $id
 * @property string $eng
 * @property string $cht
 * @property string $chs
 * @property string $jap
 * @property string $icon
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 *
 * @property BbsPost[] $bbsPosts
 */
class BbsBoard extends \common\models\BaseAR {

    const AifinSupport = 1;
    const FrontendClientNotice = 2;
    const FrontendConsultantNotice = 3;

    public $count;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'bbs_board';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['idcategory', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['idcategory', 'eng', 'cht', 'chs', 'jap'], 'required'],
            [['eng', 'cht', 'chs', 'jap', 'icon'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'eng' => Yii::t('app', 'Eng'),
            'cht' => Yii::t('app', 'Cht'),
            'chs' => Yii::t('app', 'Chs'),
            'jap' => Yii::t('app', 'Jap'),
            'icon' => Yii::t('app', 'Icon'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
            'deleted_by' => Yii::t('app', 'Deleted By'),
        ];
    }

    public function name() {
        switch (Yii::$app->language) {
            case "eng":
                return $this->eng;
                break;
            case "cht":
                return $this->cht;
                break;
            case "chs":
                return $this->chs;
                break;
            case "jap":
                return $this->jap;
                break;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBbsPosts() {
        return $this->hasMany(BbsPost::className(), ['idboard' => 'id'])
                        ->onCondition([
                            BbsPost::tableName() . '.status' => 1,
                            BbsPost::tableName() . '.idparent' => null
                        ])
                        ->orderBy("created_at desc");
    }

    public function getBbsCategory() {
        return $this->hasOne(BbsCategory::className(), ['id' => 'idcategory'])
                        ->onCondition([BbsCategory::tableName() . '.status' => 1]);
    }

    public function getCreatedBy() {
        return $this->hasOne(User::className(), ['id' => 'created_by'])
                        ->onCondition([User::tableName() . '.status' => 1]);
    }

    public function getModerator() {
        return $this->hasOne(User::className(), ['id' => 'iduser_moderator'])
                        ->onCondition([User::tableName() . '.status' => 1]);
    }

    public function getBanner() {
        return $this->hasOne(Document::className(), ['id' => 'iddoc_banner'])
                        ->onCondition([Document::tableName() . '.status' => 1]);
    }

    public function getMembers() {
        return $this->hasMany(BbsBoardMember::className(), ['idboard' => 'id'])
                        ->onCondition([
                            BbsBoardMember::tableName() . '.status' => 1,
        ]);
    }

    public function getBbsBoardTasks() {
        return $this->hasMany(BbsBoardTask::className(), ['idboard' => 'id'])
                        ->onCondition([
                            BbsBoardTask::tableName() . '.status' => 1,
        ]);
    }

    public function getBbsBoardOpenTasks() {
        return $this->hasMany(BbsBoardTask::className(), ['idboard' => 'id'])
                        ->onCondition([
                            BbsBoardTask::tableName() . '.status' => 1,
                            BbsBoardTask::tableName() . '.progress' => 1,
        ]);
    }

}
