<?php

namespace common\models;

use common\models\CrmRecord;
use common\models\CrmRecordType;
use common\models\TaskCategory;
use common\models\ClientSite;
use common\models\Progress;
use common\models\Priority;
use Yii;

/**
 * This is the model class for table "crm_record".
 *
 * @property int $id
 * @property int $idcrm_record_type
 * @property int $idclient
 * @property string $effective_date
 * @property int $client_report
 * @property int $idassignee
 * @property int $internal_report
 * @property string $record_time
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $deleted_by
 * @property int $status
 * @property string $remark
 * @property int $idprogress
 * @property int $idpriority
 * @property string $requested_at
 * @property string $reminder_at

 *
 * @property CrmActivity[] $crmActivities
 * @property CrmRecordType $crmRecordType
 * @property CrmRecordPeople[] $crmRecordPeoples
 * @property Client $client
 * @property Progress $progress
 * @property Priority $priority

 */
class CrmRecord extends \yii\db\ActiveRecord {

    const SCENARIO_BUG = 'bug';
    const SCENARIO_ENHANCEMENT = 'enhancement';
    const SCENARIO_NEW_PROJECT = 'new_project';
    const SCENARIO_CHANGELOG = 'changelog';

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'crm_record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['idcrm_record_cat', 'idcrm_record_type'], 'required'],
            [['idcrm_record_type', 'idcrm_record_cat', 'idclient', 'idassignee', 'client_report', 'internal_report',
            'created_by', 'updated_by', 'deleted_by', 'status', 'idprogress', 'idpriority'], 'integer'],
            [['effective_date', 'record_time', 'created_at', 'updated_at', 'deleted_at', 'requested_at', 'reminder_at'], 'safe'],
            [['remark', 'subject'], 'string'],
            [['idcrm_record_type', 'idcrm_record_cat'], 'exist', 'skipOnError' => true,
                'targetClass' => CrmRecordType::className(), 'targetAttribute' => ['idcrm_record_type' => 'id']],
            [['idclient'], 'exist', 'skipOnError' => true, 'targetClass' => ClientSite::className(), 'targetAttribute' => ['idclient' => 'id']],
            //   [['idprogress'], 'exist', 'skipOnError' => true, 'targetClass' => Progess::className(), 'targetAttribute' => ['idprogess' => 'id']],
            [['idclient', 'client_report', 'internal_report', 'idclient', 'idprogress', 'idpriority'], 'required',
//                'on' => [
//                    self::SCENARIO_ENHANCEMENT,
//                    self::SCENARIO_BUG,
//                    self::SCENARIO_NEW_PROJECT
//                ],
                'when' => function ($model) {
                    return in_array($model->idcrm_record_cat, [1, 2, 3]);
                }
                , 'whenClient' => "function (attribute, value) {
                        var categories = [1,2,3];
                        var selectedCat = $('#crmrecord-idcrm_record_cat').val();
                        if(categories.indexOf(parseInt(selectedCat)) !== -1) {
                            return true;
                        } else {
                            return false;
                        }
                    }"
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'idcrm_record_cat' => 'Task Category',
            'idcrm_record_type' => 'Related Function',
            'idclient' => 'Client Site ID',
            'idassignee' => 'Assignee',
            'effective_date' => 'Completion Date',
            'client_report' => 'Report By Client',
            'internal_report' => 'Report By Internal Staff',
            'record_time' => 'Record Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_by' => 'Deleted By',
            'status' => 'Status',
            'remark' => 'Remark',
            'subject' => 'Subject',
            'idprogress' => 'Progress',
            'idpriority' => 'Priority',
            'requested_at' => 'Request Date',
            'reminder_at' => 'Reminder Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrmActivities() {
        return $this->hasMany(CrmActivity::className(), ['idcrm_record' => 'id'])
                        ->orderBy(['crm_activity.created_at' => SORT_DESC]);
    }

    public function getCrmRecordType() {
        return $this->hasOne(CrmRecordType::className(), ['id' => 'idcrm_record_type']);
    }

    public function getTaskCategory() {
        return $this->hasOne(TaskCategory::className(), ['id' => 'idcrm_record_cat']);
    }

    public function getCreatedBy() {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getUpdatedBy() {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getProgress() {
        return $this->hasOne(Progress::className(), ['id' => 'idprogress']);
    }

    public function getPriority() {
        return $this->hasOne(Priority::className(), ['id' => 'idpriority']);
    }

    public function getAssigneeModel() {
        return $this->hasOne(User::className(), ['id' => 'idassignee']);
    }

    public static function getAssignee($idassignee) {
        return User::find()
                        ->where(['id' => $idassignee])
                        ->One();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrmRecordPeoples() {
        return $this->hasMany(CrmRecordPeople::className(), ['idcrm_record' => 'id']);
    }

    public function getCrmRecordCount($idclients) {
        return CrmRecord::find()->select([
                            new \yii\db\Expression("count(1)")
                        ])
                        ->where(['idprogress' => $progress])
                        ->andWhere(['IN', 'idclient', $idclients])
                        ->scalar();
    }

    public function getLastCrmActivity() {
        return $this->hasOne(CrmActivity::className(), ['idcrm_record' => 'id'])
                        ->onCondition(['crm_activity.status' => 1])
                        ->orderBy(['crm_activity.created_at' => SORT_DESC]);
    }

    public static function checkPermission($id, $idclient) {

        $crmRecord = CrmRecord::find()
                ->select('idclient')
                ->where(['id' => $id])
                ->andWhere(['idclient' => $idclient])
                ->one();

        if ($crmRecord !== null) {
            // var_dump($crmRecord); die();
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getDocuments() {
        return $this->hasMany(Document::className(), ['idcr' => 'id']);
    }

    public function getClientSite() {
        return $this->hasOne(ClientSite::className(), ['id' => 'idclient'])
                        ->onCondition(['client_site.status' => 1]);
    }

}
