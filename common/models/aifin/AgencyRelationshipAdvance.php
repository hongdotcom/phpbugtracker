<?php

namespace common\models\aifin;

use Yii;

/**
 * This is the model class for table "agency_relationship_advance".
 *
 * @property integer $idpeople
 * @property integer $people_role_type
 * @property integer $agent0_code
 * @property integer $agent1_code
 * @property integer $agent2_code
 * @property integer $agent3_code
 * @property integer $agent4_code
 * @property integer $agent5_code
 * @property integer $agent6_code
 * @property integer $agent7_code
 * @property integer $agent8_code
 * @property integer $agent9_code
 * @property integer $agent10_code
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 * @property integer $status
 */
class AgencyRelationshipAdvance extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'agency_relationship_advance';
    }

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['idpeople'], 'required'],
            [['idpeople', 'people_role_type', 'agent0_code', 'agent1_code', 'agent2_code', 'agent3_code', 'agent4_code', 'agent5_code', 'agent6_code', 'agent7_code', 'agent8_code', 'agent9_code', 'agent10_code', 'created_by', 'updated_by', 'deleted_by', 'status'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'idpeople' => 'Idpeople',
            'people_role_type' => 'People Role Type',
            'agent0_code' => 'Agent0 Code',
            'agent1_code' => 'Agent1 Code',
            'agent2_code' => 'Agent2 Code',
            'agent3_code' => 'Agent3 Code',
            'agent4_code' => 'Agent4 Code',
            'agent5_code' => 'Agent5 Code',
            'agent6_code' => 'Agent6 Code',
            'agent7_code' => 'Agent7 Code',
            'agent8_code' => 'Agent8 Code',
            'agent9_code' => 'Agent9 Code',
            'agent10_code' => 'Agent10 Code',
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
     * Return all DOWNLINES for $idpeople (excluding UPLINES)
     * @param integer $idpeople
     * @return array Including the incoming idpeople
     */
    public static function getConsultantWholeTree($idpeople) {
        /* Find out what Agent Level he is */
        $people_role_type = self::find()
                ->select(['people_role_type'])
                ->where([
                    'status' => 1,
                    'idpeople' => $idpeople,
                ])
                ->scalar();

        $result = [];

        if ($people_role_type >= 1 and $people_role_type <= 10) {

            $target_column = "agent" . $people_role_type . "_code";

            // get all DOWNLINES for $idpeople
            $result = self::find()
                    ->select(['idpeople'])
                    ->where([
                        $target_column => $idpeople,
                        'status' => 1
                    ])
                    //->where("idpeople <> $target_column");
                    ->orderBy(['idpeople' => SORT_ASC])
                    ->column();

            /* Append incoming idpeople to result */
            $result[] = $idpeople;

            /* remove duplilcated array elements */
            $result = array_unique($result);
        }
        return $result;
    }

    public static function getAgentByLevel($level) {
        return self::find()
                        ->select(['idpeople'])
                        ->where([
                            'people_role_type' => $level,
                            'status' => 1
                        ])
                        ->column();
    }

    public function getPeople() {
        return $this->hasOne(People::className(), ['id' => 'idpeople'])
                        ->onCondition([People::tableName() . '.status' => 1]);
    }

    public function getAgent1() {
        return $this->hasOne(People::className(), ['id' => 'agent1_code']);
    }

    public function getAgent2() {
        return $this->hasOne(People::className(), ['id' => 'agent2_code']);
    }

    public function getAgent3() {
        return $this->hasOne(People::className(), ['id' => 'agent3_code']);
    }

    public function getAgent4() {
        return $this->hasOne(People::className(), ['id' => 'agent4_code']);
    }

    public function getAgent5() {
        return $this->hasOne(People::className(), ['id' => 'agent5_code']);
    }

    public function getAgent6() {
        return $this->hasOne(People::className(), ['id' => 'agent6_code']);
    }

    public function getAgent7() {
        return $this->hasOne(People::className(), ['id' => 'agent7_code']);
    }

    public function getAgent8() {
        return $this->hasOne(People::className(), ['id' => 'agent8_code']);
    }

    public function getAgent9() {
        return $this->hasOne(People::className(), ['id' => 'agent9_code']);
    }

    public function getAgent10() {
        return $this->hasOne(People::className(), ['id' => 'agent10_code']);
    }

    /**
     * Return all UPLINES & DOWNLINES for $idpeople
     * @param integer $idpeople
     * @return array Including the incoming idpeople
     */
    public static function getConsultantWholeUpDownTree($idpeople) {

        $tree = null;

        $result = self::find()
                ->where([
                    'idpeople' => $idpeople,
                    'status' => 1
                ])
                ->one();

        if ($result) {
            for ($i = 2; $i < 10; $i++) {
                if ($result->{"agent$i" . "_code"}) {
                    $tree[] = $result->{"agent$i" . "_code"};
                }
            }
        }
        return $tree;
    }

    /**
     * Return all DOWNLINES for $idpeople (excluding UPLINES)
     * @param integer $idpeople
     * @return array Including the incoming idpeople
     */
    public static function getWholeDownlineTree($idpeople) {
        /* Find out what Agent Level he is */
        $people_role_type = self::find()
                ->select(['people_role_type'])
                ->where([
                    'status' => 1,
                    'idpeople' => $idpeople,
                ])
                ->scalar();

        if (!$people_role_type) {
            return FALSE;
        }

        $result = null;

        $target_column = "agent" . $people_role_type . "_code";

        $result = self::find()
                ->select(['idpeople', 'people_role_type'])
                ->joinWith(['people'])
                ->where([
                    $target_column => $idpeople,
                    self::tableName() . '.status' => 1
                ])
                ->orderBy(['people_role_type' => SORT_ASC])
//                ->asArray()
                ->all();

        return $result;
    }

    public static function getDirectDownline($idpeople) {
        /* Find out what Agent Level he is */
        $people_role_type = self::find()
                ->select(['people_role_type'])
                ->where([
                    'status' => 1,
                    'idpeople' => $idpeople,
                ])
                ->scalar();

        $result = null;

        $target_column = "agent" . $people_role_type . "_code";

        $result = self::find()
                ->select(['idpeople', 'people_role_type'])
                ->joinWith(['people'])
                ->where([
                    $target_column => $idpeople,
                    self::tableName() . '.status' => 1
                ])
                ->orderBy(['people_role_type' => SORT_ASC])
                ->all();

        return $result;
    }

}
