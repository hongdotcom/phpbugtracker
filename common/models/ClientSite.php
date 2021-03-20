<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "client".
 *
 * @property int $id
 * @property string $client_name
 * @property int $status
 * @property CrmRecord[] $crmRecords
 */
class ClientSite extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'client_site';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'client_name', 'status'], 'required'],
            [['id', 'status'], 'integer'],
            [['client_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'client_name' => 'Client Name',
            'status' => 'Status',
        ];
    }

    public function getCrmRecords() {
        return $this->hasMany(CrmRecord::className(), ['idclient' => 'id']);
    }

    public static function getClientMainEmail($id) {
        return ClientSite::find()
                        ->select('client_main_email')
                        ->where(['id' => $id])
                        ->one();
    }

    public function name() {
        return $this->client_name;
    }

}
