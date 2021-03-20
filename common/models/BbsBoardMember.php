<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bbs_category_member".
 *
 * @property int $id
 * @property int $idcategory
 * @property int $iduser
 * @property int $status
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $deleted_at
 * @property int $deleted_by
 *
 * @property User $user
 * @property BbsCategory $category
 */
class BbsBoardMember extends \common\models\BaseAR {

    public $fullname;
    public $fullname_locale;
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'bbs_board_member';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['idboard'], 'required'],
            [['idboard', 'iduser', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['iduser'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['iduser' => 'id']],
            [['idboard'], 'exist', 'skipOnError' => true, 'targetClass' => BbsBoard::className(), 'targetAttribute' => ['idboard' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'idcategory' => 'Idcategory',
            'iduser' => 'Iduser',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'iduser'])
                        ->onCondition([User::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory() {
        return $this->hasOne(BbsCategory::className(), ['id' => 'idcategory'])
                        ->onCondition([BbsCategory::tableName() . '.status' => 1]);
    }

}
