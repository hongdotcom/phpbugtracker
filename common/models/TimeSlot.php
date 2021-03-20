<?php

namespace common\models;

use Yii;

class TimeSlot extends \yii\db\ActiveRecord {

    const FULLDAY = 99;
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'timeslot';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['element_type', 'element_key', 'value_eng', 'value_cht', 'value_chs', 'value_jap'], 'required'],
            [['seq', 'status'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['element_type'], 'string', 'max' => 50],
            [['element_key', 'value_eng', 'value_cht', 'value_chs', 'value_jap'], 'string', 'max' => 80],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'seq' => 'Sequence',
            'value_eng' => 'Value Eng',
            'value_cht' => 'Value Cht',
            'value_chs' => 'Value Chs',
            'value_jap' => 'Value Jap',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function name($lang = null) {
        if (!$lang) {
            $lang = \common\helpers\AifinHelper::getLang();
        }
        
        switch ($lang) {
            case "eng":
                return $this->value_eng;
                break;
            case "cht":
                return $this->value_cht;
                break;
            case "chs":
                return $this->value_chs;
                break;
            case "jap":
                return $this->value_eng;
                break;
        }
    }

//    public function getFreebusys() {
//        return $this->hasMany(FreeBusy::className(), ['idtimeslot' => 'id'])
//                        ->onCondition([FreeBusy::tableName() . '.status' => 1]);
//    }

}
