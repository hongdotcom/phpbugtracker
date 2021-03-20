<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_record_type".
 *
 * @property int $id
 * @property int $seq
 * @property int $idcrm_record_cat
 * @property string $crm_record_type_name_eng
 * @property string $crm_record_type_name_cht
 * @property string $crm_record_type_name_chs
 * @property string $crm_record_type_name_jap
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $deleted_by
 * @property int $status
 *
 * @property CrmRecord[] $crmRecords
 * @property CrmRecordCategory $crmRecordCat
 */
class CrmRecordType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'crm_record_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['seq', 'idcrm_record_cat', 'created_by', 'updated_by', 'deleted_by', 'status'], 'integer'],
            [['idcrm_record_cat'], 'required'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['crm_record_type_name_eng', 'crm_record_type_name_cht', 'crm_record_type_name_chs', 'crm_record_type_name_jap'], 'string', 'max' => 45],
            [['idcrm_record_cat'], 'exist', 'skipOnError' => true, 'targetClass' => CrmRecordCategory::className(), 'targetAttribute' => ['idcrm_record_cat' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'seq' => 'Seq',
            'idcrm_record_cat' => 'Idcrm Record Cat',
            'crm_record_type_name_eng' => 'Crm Record Type Name Eng',
            'crm_record_type_name_cht' => 'Crm Record Type Name Cht',
            'crm_record_type_name_chs' => 'Crm Record Type Name Chs',
            'crm_record_type_name_jap' => 'Crm Record Type Name Jap',
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
    public function getCrmRecords()
    {
        return $this->hasMany(CrmRecord::className(), ['idcrm_record_type' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrmRecordCat()
    {
        return $this->hasOne(CrmRecordCategory::className(), ['id' => 'idcrm_record_cat']);
    }
}
