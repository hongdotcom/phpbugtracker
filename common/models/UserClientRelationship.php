<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_client_relationship".
 *
 * @property int $id
 * @property int $iduser
 * @property int $idclient
 * @property int $status
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $deleted_at
 * @property int $deleted_by
 *
 * @property UserClientRelationship $client
 * @property UserClientRelationship[] $userClientRelationships
 * @property User $user
 */
class UserClientRelationship extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'user_client_relationship';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['iduser', 'idclient'], 'required'],
            [['iduser', 'idclient', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['idclient'], 'exist', 'skipOnError' => true, 'targetClass' => UserClientRelationship::className(), 'targetAttribute' => ['idclient' => 'id']],
            [['iduser'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['iduser' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'iduser' => 'Iduser',
            'idclient' => 'Idclient',
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
    public function getClient() {
        return $this->hasOne(UserClientRelationship::className(), ['id' => 'idclient']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserClientRelationships() {
        return $this->hasMany(UserClientRelationship::className(), ['idclient' => 'id'])->idclient;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'iduser']);
    }

    public static function getAllReceipients($idclient) {
        $idusers = UserClientRelationship::find()
                ->select('iduser')
                ->where(['idclient' => $idclient])
                ->andWhere(['recipient' => '1'])
                ->createCommand()
                ->queryColumn();

        return $idusers;
    }

}
