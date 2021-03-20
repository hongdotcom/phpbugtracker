<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_activity".
 *
 * @property int $id
 * @property int $idcrm_record
 * @property string $action_date
 * @property string $internal_remark
 * @property string $agent_remark
 * @property string $client_remark
 * @property string $reminder_date
 * @property int $email_notification_client
 * @property int $email_notification_consultant
 * @property int $sms_client
 * @property int $sms_consultant
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $deleted_by
 * @property int $status
 * @property int $progress
 * @property string $request_date
 * @property int $request_to
 * @property string $received_date
 * @property int $received_from
 * @property string $delivery_date
 * @property int $delivery_to
 * @property string $provider_remark
 * @property int $email_client
 *
 * @property CrmRecord $crmRecord
 */
class CrmActivity extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'crm_activity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['idcrm_record'], 'required'],
            [['idcrm_record', 'email_notification_client', 'email_notification_consultant', 'sms_client', 'sms_consultant', 'created_by', 'updated_by', 'deleted_by', 'status', 'progress', 'request_to', 'received_from', 'delivery_to', 'email_client'], 'integer'],
            [['action_date', 'reminder_date', 'created_at', 'updated_at', 'deleted_at', 'request_date', 'received_date', 'delivery_date'], 'safe'],
            [['internal_remark', 'agent_remark', 'client_remark', 'provider_remark'], 'string'],
            [['idcrm_record'], 'exist', 'skipOnError' => true, 'targetClass' => CrmRecord::className(), 'targetAttribute' => ['idcrm_record' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'idcrm_record' => 'CRM Record ID',
            'action_date' => 'Action Date',
            'internal_remark' => 'Internal Remark',
            'agent_remark' => 'Agent Remark',
            'client_remark' => 'Client Remark',
            'reminder_date' => 'Reminder Date',
            'email_notification_client' => 'Email Notification Client',
            'email_notification_consultant' => 'Email Notification Consultant',
            'sms_client' => 'Sms Client',
            'sms_consultant' => 'Sms Consultant',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_by' => 'Deleted By',
            'status' => 'Status',
            'progress' => 'Progress',
            'request_date' => 'Request Date',
            'request_to' => 'Request To',
            'received_date' => 'Received Date',
            'received_from' => 'Received From',
            'delivery_date' => 'Delivery Date',
            'delivery_to' => 'Delivery To',
            'provider_remark' => 'Provider Remark',
            'email_client' => 'Email Client',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrmRecord() {
        return $this->hasOne(CrmRecord::className(), ['id' => 'idcrm_record']);
    }

    public function getDocuments() {
        return $this->hasMany(Document::className(), ['idca' => 'id']);
    }

    public function getCreatedBy() {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getUpdatedBy() {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

}
