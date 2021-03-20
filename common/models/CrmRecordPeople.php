<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_record_people".
 *
 * @property int $id
 * @property int $idcrm_record
 * @property int $idpeople
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $deleted_by
 * @property int $status
 *
 * @property CrmRecord $crmRecord
 */
class CrmRecordPeople extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'crm_record_people';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idcrm_record', 'idpeople'], 'required'],
            [['idcrm_record', 'idpeople', 'created_by', 'updated_by', 'deleted_by', 'status'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['idcrm_record'], 'exist', 'skipOnError' => true, 'targetClass' => CrmRecord::className(), 'targetAttribute' => ['idcrm_record' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idcrm_record' => 'Idcrm Record',
            'idpeople' => 'Idpeople',
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
    public function getCrmRecord()
    {
        return $this->hasOne(CrmRecord::className(), ['id' => 'idcrm_record']);
    }
}
