<?php

namespace common\models\aifin;

use Yii;

/**
 * This is the model class for table "plan_people_type".
 *
 * @property integer $id
 * @property string $pp_type_name_eng
 * @property string $pp_type_name_cht
 * @property string $pp_type_name_chs
 * @property string $pp_type_name_jap
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @property PlanPeople[] $planPeoples
 */
class PlanPeopleType extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'plan_people_type';
    }

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['pp_type_name_eng', 'pp_type_name_cht', 'pp_type_name_chs', 'pp_type_name_jap'], 'string', 'max' => 45],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanPeoples() {
        return $this->hasMany(PlanPeople::className(), ['role_type' => 'id']);
    }

}
