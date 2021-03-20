<?php

namespace common\models\aifin;

use common\helpers\IfapHelper;
use Yii;
use common\models\PlanPeopleType;
use yii\db\Expression;

/**
 * This is the model class for table "plan_people".
 *
 * @property integer $id
 * @property integer $idplan
 * @property integer $idpeople
 * @property integer $idpeople_info_set
 * @property integer $same_as_holder
 * @property integer $role_type
 * @property integer $sequence
 * @property string $pu_percentage
 * @property string $effective_date
 * @property string $end_date
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @property PlanPeopleType $roleType
 * @property People $idpeople0
 * @property Plan $idplan0
 * @property PlanPeople[] $relatedPlanPeoples
 * @property PeoplePhone[] $phones
 * @property PeopleIdentification[] $idens
 * @property PeopleAddress[] $addresses
 * @property PeopleInfoSet[] $infoSets
 * @property People $people
 */
class PlanPeople extends \yii\db\ActiveRecord {

    const HOLDER = 1;
    const WRITING = 2;
    const SERVICING = 3;
    const SIGNATURE = 4;
    const INSURED = 5;

    public $role;
    public $roleId = 0;
    public $bv_pct;

    //public $same_as_holder = -1; // Dont use ZERO

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'plan_people';
    }

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['same_as_holder', 'idpeople_info_set', 'idplan', 'idpeople', 'role_type', 'sequence', 'status', 'smoker', 'risk_level',
            'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['pu_percentage'], 'required'],
            [['pu_percentage'], 'number'],
            [['idplan', 'idpeople', 'role_type', 'sequence', 'status', 'effective_date', 'end_date', 'pu_percentage'], 'safe'],
            [['role_type'], 'exist', 'skipOnError' => true, 'targetClass' => PlanPeopleType::className(), 'targetAttribute' => ['role_type' => 'id']],
            [['idpeople'], 'exist', 'skipOnError' => true, 'targetClass' => People::className(), 'targetAttribute' => ['idpeople' => 'id']],
            [['idplan'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::className(), 'targetAttribute' => ['idplan' => 'id']],
            [['status'], 'default', 'value' => 1],
            [['same_as_holder'], 'default', 'value' => 0],
        ];
    }

    public function checkDate() {
        if ($this->effective_date > $this->end_date)
            $this->addError('effective_date', str_replace('{{idpeople}}', $this->idpeople, WebLang::$t['plan_error_consultant_date']));
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoleType() {
        return $this->hasOne(PlanPeopleType::className(), ['id' => 'role_type'])
                        ->onCondition([PlanPeopleType::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeople() {
        return $this->hasOne(People::className(), ['id' => 'idpeople'])
//                        ->from(People::tableName() . ' e')
                        ->onCondition(['<>', People::tableName() . '.status', People::DELETED]);
    }

    public function getPeopleRole() {
        return $this->hasOne(PeopleRole::className(), ['idpeople' => 'idpeople'])
                        ->onCondition([PeopleRole::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlan() {
        return $this->hasOne(Plan::className(), ['id' => 'idplan'])
                        ->onCondition(['<>', Plan::tableName() . '.status', Plan::DELETED]);
    }

    public static function getEffectiveConsultants($idplan, $roleType) {
        $today = date("Y-m-d");

        $query = self::find()
                ->where(['plan_people.idplan' => $idplan])
                ->andWhere(['plan_people.role_type' => $roleType])
                ->andWhere(['<=', "plan_people.effective_date", $today])
                ->andWhere(['>=', "plan_people.end_date", $today])
                ->andWhere(['plan_people.status' => 1]);
//                ->all();
//        echo $query->createCommand()->rawSql;
        return $query->all();
    }

    public function getHolderPlanIdentification() {
        return $this->hasMany(PlanIdentification::className(), ['idplan_people' => 'id'])
                        ->from(PlanIdentification::tableName() . ' holderidentification');
    }

    public function getHolderPlanPhone() {
        return $this->hasMany(PlanPhone::className(), ['idplan_people' => 'id'])
                        ->from(PlanPhone::tableName() . ' holderphone');
    }

    public function getHolderPlanAddress() {
        return $this->hasMany(PlanAddress::className(), ['idplan_people' => 'id'])
                        ->from(PlanAddress::tableName() . ' holderaddress');
    }

    public function getInsuredPlanIdentification() {
        return $this->hasMany(PlanIdentification::className(), ['idplan_people' => 'id'])
                        ->from(PlanIdentification::tableName() . ' insuredidentification');
    }

    public function getInsuredPlanPhone() {
        return $this->hasMany(PlanPhone::className(), ['idplan_people' => 'id'])
                        ->from(PlanPhone::tableName() . ' insuredphone');
    }

    public function getInsuredPlanAddress() {
        return $this->hasMany(PlanAddress::className(), ['idplan_people' => 'id'])
                        ->from(PlanAddress::tableName() . ' insuredaddress');
    }

    public function getRelatedPlanPeoples() {
        return $this->hasMany(PlanPeople::className(), ['idplan' => 'idplan']);
    }

    public function getPhones() {
        return $this->hasMany(PeoplePhone::className(), ['idset' => 'id'])
                        ->from(PeoplePhone::tableName() . ' ' . $this->role . 'Phone')
                        ->onCondition([$this->role . 'Phone.status' => 1])
                        ->viaTable(PeopleInfoSet::tableName(), ['id' => 'idpeople_info_set'], function ($query) {
                            return $query->from(PeopleInfoSet::tableName() . ' ' . $this->role . 'PhoneSet');
                        });
    }

    public function getIdens() {
        return $this->hasMany(PeopleIdentification::className(), ['idset' => 'id'])
                        ->from(PeopleIdentification::tableName() . ' ' . $this->role . 'Iden')
                        ->onCondition([$this->role . 'Iden.status' => 1])
                        ->viaTable(PeopleInfoSet::tableName(), ['id' => 'idpeople_info_set'], function ($query) {
                            return $query->from(PeopleInfoSet::tableName() . ' ' . $this->role . 'IdenSet');
                        });
    }

    public function getAddresses() {
        return $this->hasMany(PeopleAddress::className(), ['idset' => 'id'])
                        ->from(PeopleAddress::tableName() . ' ' . $this->role . 'Address')
                        ->onCondition([$this->role . 'Address.status' => 1])
                        ->viaTable(PeopleInfoSet::tableName(), ['id' => 'idpeople_info_set'], function ($query) {
                            return $query->from(PeopleInfoSet::tableName() . ' ' . $this->role . 'AddressSet');
                        });
    }

    public function getInfoSets() {
        return $this->hasMany(PeopleInfoSet::className(), ['idpeople' => 'idpeople'])
                        ->from(PeopleInfoSet::tableName() . ' ' . $this->role . 'InfoSet');
    }

    /**
     * getWritingPersistencyRatio
     * @param array $arrParams idpeople(int), db(object), months(int), treeMode (bool)
     * @return array
     */
    public static function getWritingPersistencyRatio($arrParams) {
        // acc_date
        $startAccDate = "";
        $endAccDate = "";
        $persistencyRatioIncludeTransferIn = Settings::$key['persistency_ratio_includes_transfer_in'];

        if (isset($arrParams['idpeople'])) {
            $idpeople = $arrParams['idpeople'];
        } else {
            return -1;
        }

        if (isset($arrParams['months'])) {
            $months = $arrParams['months'];
        } else {
            $months = 0; // default lifetime
        }

        if (!isset($arrParams['treeMode'])) {
            $arrParams['treeMode'] = TRUE;
        }
        if ($arrParams['treeMode']) {
            $arrIdpeople = AgencyRelationshipAdvance::getConsultantWholeTree($arrParams['idpeople']);
        }

        $today = date("Y-m-d");

        if ($months > 0) {
            $startAccDate = date('Y-m-1', strtotime("-$months months", strtotime($today)));
            ;
            $endAccDate = date('Y-m-1', strtotime("+1 months", strtotime($today)));
            ;
        }
        $arrReturn = array();

        /*
         * NB
         */
        $sqlNB = "select count(distinct p.id) as plans" .
                " from plan_people pp" .
                " join plan p on p.id=pp.idplan and p.status=1" .
                " join people e on e.id=pp.idpeople and e.status=1" .
                " join status_detail s on s.id=p.plan_status" .
                " join status_master sm on sm.id=s.idstatus_master2" .
                " WHERE sm.id=1" .
                " and pp.role_type=2" .
                " and pp.status=1";

        if ($persistencyRatioIncludeTransferIn <> 1) {
            $sqlNB .= " and p.transfer_in <> 1";
        }

        if (!empty($startAccDate)) {
            $sqlNB .= " AND p.acc_date >= '$startAccDate'";
        }
        if (!empty($endAccDate)) {
            $sqlNB .= " AND p.acc_date < '$endAccDate'";
        }

        $sqlActive = "select count(distinct p.id) as plans" .
                " from plan_people pp" .
                " join plan p on p.id=pp.idplan and p.status=1" .
                " join people e on e.id=pp.idpeople and e.status=1" .
                " join status_detail s on s.id=p.plan_status" .
                " join status_master sm on sm.id=s.idstatus_master2" .
                " WHERE sm.id=2" .
                " and pp.role_type=2" .
                " and pp.status=1";

        if ($persistencyRatioIncludeTransferIn <> 1) {
            $sqlActive .= " and p.transfer_in <> 1";
        }

        if (!empty($startAccDate)) {
            $sqlActive .= " AND p.acc_date >= '$startAccDate'";
        }
        if (!empty($endAccDate)) {
            $sqlActive .= " AND p.acc_date < '$endAccDate'";
        }

        $sqlInactive = " select count(distinct p.id) as plans" .
                " from plan_people pp" .
                " join plan p on p.id=pp.idplan and p.status=1" .
                " join people e on e.id=pp.idpeople and e.status=1" .
                " join status_detail s on s.id=p.plan_status" .
                " join status_master sm on sm.id=s.idstatus_master2" .
                " WHERE sm.id=6" .
                " and pp.role_type=2" .
                " and pp.status=1";

        if ($persistencyRatioIncludeTransferIn <> 1) {
            $sqlInactive .= " and p.transfer_in <> 1";
        }

        if (!empty($startAccDate)) {
            $sqlInactive .= " AND p.acc_date >= '$startAccDate'";
        }
        if (!empty($endAccDate)) {
            $sqlInactive .= " AND p.acc_date < '$endAccDate'";
        }

        if ($arrParams['treeMode']) {
            $strIdWriting = IfapHelper::arrayToInClause($arrIdpeople);
            $sqlNB .= " AND pp.idpeople IN $strIdWriting";
            $sqlActive .= " AND pp.idpeople IN $strIdWriting";
            $sqlInactive .= " AND pp.idpeople IN $strIdWriting";
        } else {
            $sqlNB .= " AND pp.idpeople=" . $arrParams['idpeople'];
            $sqlActive .= " AND pp.idpeople=" . $arrParams['idpeople'];
            $sqlInactive .= " AND pp.idpeople=" . $arrParams['idpeople'];
        }

        $sql = $sqlNB . " UNION ALL " . $sqlActive . " UNION ALL " . $sqlInactive;

        $results = Yii::$app->db->createCommand($sql)->queryAll();

        $ratio = 0;
        $inactivePlans = 0;
        $activePlans = 0;

        if (count($results) == 3) {
            $nbPlans = $results[0]['plans'];
            $activePlans = $results[1]['plans'];
            $inactivePlans = $results[2]['plans'];

            if ($activePlans > 0) {
                $ratio = round(((($activePlans - $inactivePlans) / $activePlans) * 100), 2);
            } else {
                $ratio = 0;
            }
        }

        $arrReturn = array(
            'ratio' => $ratio,
            'nb' => $nbPlans,
            'active' => $activePlans,
            'cancelled' => $inactivePlans,
            'idpeople' => $idpeople
        );
        return $arrReturn;
    }

    /*
     * Call this only when instantiating new empty record
     */

    public function setDefaultValues() {
        $this->effective_date = "1970-1-1";
        $this->end_date = "9999-12-31";
        $this->pu_percentage = 100;
    }

    /**
     * Check the effective date range vs targetDate ONLY
     * @param string $targetDate
     * @return boolean
     */
    public function isEffectiveConsultant($targetDate = NULL) {
        if (!$targetDate) {
            if ($this->end_date == '9999-12-31')
                return TRUE;
        } else {
            if ($this->effective_date <= $targetDate && $this->end_date >= $targetDate)
                return TRUE;
        }
        return FALSE;
    }

    public static function getAgentsByPlan($idplan) {
        $planPeople = new static();
        $roleId = $planPeople->roleId;

        $query = static::find()
                ->where([
            'status' => 1,
            'idplan' => $idplan
        ]);

        if ($roleId > 0) {
            $query->andWhere(['role_type' => $roleId]);
        } else
            $query->andWhere(['IN', 'role_type', [self::WRITING, self::SERVICING, self::SIGNATURE]]);

        return $query->all();
    }

    public function getAgent1() {
        return $this->hasOne(People::className(), ['id' => 'agent1_code'])->alias('agent1_people')
                        ->onCondition(['agent1_people.status' => 1]);
    }

    public function getAgent2() {
        return $this->hasOne(People::className(), ['id' => 'agent2_code'])->alias('agent2_people')
                        ->onCondition(['agent2_people.status' => 1]);
    }

    public function getAgent3() {
        return $this->hasOne(People::className(), ['id' => 'agent3_code'])->alias('agent3_people')
                        ->onCondition(['agent3_people.status' => 1]);
    }

    public function getAgent4() {
        return $this->hasOne(People::className(), ['id' => 'agent4_code'])->alias('agent4_people')
                        ->onCondition(['agent4_people.status' => 1]);
    }

    public function getAgent5() {
        return $this->hasOne(People::className(), ['id' => 'agent5_code'])->alias('agent5_people')
                        ->onCondition(['agent5_people.status' => 1]);
    }

    public function getAgent6() {
        return $this->hasOne(People::className(), ['id' => 'agent6_code'])->alias('agent6_people')
                        ->onCondition(['agent6_people.status' => 1]);
    }

    public function getAgent7() {
        return $this->hasOne(People::className(), ['id' => 'agent7_code'])->alias('agent7_people')
                        ->onCondition(['agent7_people.status' => 1]);
    }

    public function getAgent8() {
        return $this->hasOne(People::className(), ['id' => 'agent8_code'])->alias('agent8_people')
                        ->onCondition(['agent8_people.status' => 1]);
    }

    public function getAgent9() {
        return $this->hasOne(People::className(), ['id' => 'agent9_code'])->alias('agent9_people')
                        ->onCondition(['agent9_people.status' => 1]);
    }

    public function getAgent10() {
        return $this->hasOne(People::className(), ['id' => 'agent10_code'])->alias('agent10_people')
                        ->onCondition(['agent10_people.status' => 1]);
    }

}
