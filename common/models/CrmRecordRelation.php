<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_record_relation".
 *
 * @property int $id
 * @property int $idcrm_record
 * @property int $idpeople
 * @property int $idplan
 * @property int $status
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $deleted_at
 * @property int $deleted_by
 */
class CrmRecordRelation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'crm_record_relation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idcrm_record'], 'required'],
            [['idcrm_record', 'idpeople', 'idplan', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'idplan' => 'Idplan',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }
}
