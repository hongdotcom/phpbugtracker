<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_role_relationship".
 *
 * @property int $id
 * @property int $iduser
 * @property int $idrole
 * @property int $status
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $deleted_at
 * @property int $deleted_by
 *
 * @property UserRole $role
 * @property User $user
 */
class UserRoleRelationship extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_role_relationship';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['iduser', 'idrole'], 'required'],
            [['iduser', 'idrole', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['idrole'], 'exist', 'skipOnError' => true, 'targetClass' => UserRole::className(), 'targetAttribute' => ['idrole' => 'id']],
            [['iduser'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['iduser' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iduser' => 'Iduser',
            'idrole' => 'Idrole',
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
    public function getRole()
    {
        return $this->hasOne(UserRole::className(), ['id' => 'idrole']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'iduser']);
    }
}
