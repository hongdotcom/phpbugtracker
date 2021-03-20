<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_activity_template".
 *
 * @property int $id
 * @property int $idcrt
 * @property string $short_desc
 * @property string $eng
 * @property string $cht
 * @property string $chs
 * @property string $jap
 * @property int $order
 * @property int $status
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $deleted_at
 * @property int $deleted_by
 */
class CrmActivityTemplate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'crm_activity_template';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idcrt', 'order', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['eng', 'cht', 'chs', 'jap'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['short_desc'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idcrt' => 'Idcrt',
            'short_desc' => 'Short Desc',
            'eng' => 'Eng',
            'cht' => 'Cht',
            'chs' => 'Chs',
            'jap' => 'Jap',
            'order' => 'Order',
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
