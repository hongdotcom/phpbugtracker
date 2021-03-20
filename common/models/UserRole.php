<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_role".
 *
 * @property int $id
 * @property string $eng
 * @property string $cht
 * @property string $chs
 * @property string $jap
 * @property string $kor
 * @property string $landing_page
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $deleted_at
 * @property int $deleted_by
 * @property int $status
 *
 * @property UserRoleRelationship[] $userRoleRelationships
 */
class UserRole extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_role';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by', 'status'], 'integer'],
            [['eng', 'cht', 'chs', 'jap', 'kor'], 'string', 'max' => 255],
            [['landing_page'], 'string', 'max' => 60],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'eng' => 'Eng',
            'cht' => 'Cht',
            'chs' => 'Chs',
            'jap' => 'Jap',
            'kor' => 'Kor',
            'landing_page' => 'Landing Page',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoleRelationships()
    {
        return $this->hasMany(UserRoleRelationship::className(), ['idrole' => 'id']);
    }
}
