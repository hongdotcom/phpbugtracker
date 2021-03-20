<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "task_category".
 *
 * @property int $id
 * @property string $cat_name_eng
 * @property string $cat_name_cht
 * @property string $cat_name_chs
 * @property string $cat_name_jap
 * @property int $status
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $deleted_at
 * @property int $deleted_by
 *
 * 
 */
class TaskCategory extends \yii\db\ActiveRecord {

    const MEMO = 9;
    const WORKLOG = 11;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'task_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['task_name_eng', 'cat_name_cht', 'cat_name_chs', 'cat_name_jap'], 'required'],
            [['status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['task_name_eng', 'cat_name_cht', 'cat_name_chs', 'cat_name_jap'], 'string', 'max' => 80],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'task_name_eng' => 'Task Category',
            'cat_name_cht' => 'Cat Name Cht',
            'cat_name_chs' => 'Cat Name Chs',
            'cat_name_jap' => 'Cat Name Jap',
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
    public function getCrmRecords() {
        return $this->hasMany(CrmRecord::className(), ['idcrm_record_cat' => 'id']);
    }

    public function name() {
        $lang = Yii::$app->language;
        return $this->{"task_name_$lang"};
    }
}
