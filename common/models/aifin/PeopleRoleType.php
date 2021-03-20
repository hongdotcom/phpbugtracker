<?php

namespace common\models\aifin;

use Yii;

/**
 * This is the model class for table "people_role_type".
 *
 * @property integer $id
 * @property string $role_type_name_eng
 * @property string $role_type_name_cht
 * @property string $role_type_name_chs
 * @property string $role_type_name_jap
 * @property integer $display_order
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 * @property integer $status
 *
 * @property PeopleRole[] $peopleRoles
 */
class PeopleRoleType extends \yii\db\ActiveRecord {

    const AGENT1 = 1;
    const LEVEL2 = 2;
    const LEVEL3 = 3;
    const LEVEL4 = 4;
    const LEVEL5 = 5;
    const LEVEL6 = 6;
    const LEVEL7 = 7;
    const LEVEL8 = 8;
    const LEVEL9 = 9;
    const LEVEL10 = 10;
    const CLIENT = 11;
    const STAFF = 12;
    const COMPANY = 15;
    const CONTACTPOINT = 21;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'people_role_type';
    }

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['role_type_name_eng'], 'required'],
            [['display_order', 'created_by', 'updated_by', 'deleted_by', 'status'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['role_type_name_eng', 'role_type_name_cht', 'role_type_name_chs', 'role_type_name_jap'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'role_type_name_eng' => 'Role Type Name Eng',
            'role_type_name_cht' => 'Role Type Name Cht',
            'role_type_name_chs' => 'Role Type Name Chs',
            'role_type_name_jap' => 'Role Type Name Jap',
            'display_order' => 'Display Order',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_by' => 'Deleted By',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeopleRoles() {
        return $this->hasMany(PeopleRole::className(), ['idpeople_role_type' => 'id']);
    }

    public static function getList() {
        return self::find()
                        ->indexBy('id')
                        ->asArray()
                        ->all();
    }

    public static function getPeopleRoleName($roleType) {
        return self::find()
                        ->select(['role_type_name_' . Yii::$app->language])
                        ->where(['id' => $roleType])
                        ->scalar();
    }

    public function name() {
        return $this->{'role_type_name_' . Yii::$app->language};
    }

}
