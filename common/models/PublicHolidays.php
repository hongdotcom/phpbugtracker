<?php

namespace common\models;

use Yii;

class PublicHolidays extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public_holidays';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_name_eng', 'event_name_cht', 'event_name_chs', 'event_name_jap', 'event_name_kor', 'event_date'], 'required'],
            [['event_date', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['event_name_eng', 'event_name_cht', 'event_name_chs', 'event_name_jap', 'event_name_kor', ], 'string', 'max' => 100],
        ];
    }

}
