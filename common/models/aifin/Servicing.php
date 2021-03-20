<?php

namespace common\models\aifin;

use Yii;
use common\models\aifin\PlanPeople;
use common\models\aifin\PlanPeopleType;
use yii\db\Expression;

class Servicing extends PlanPeople {

    public $roleId = self::SERVICING;

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    public function rules() {
        return [
            [['idplan', 'idpeople', 'role_type', 'sequence', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['idpeople', 'effective_date', 'end_date'], 'required'],
            [['pu_percentage'], 'number'],
            [['idplan', 'idpeople', 'role_type', 'sequence', 'status', 'pu_percentage'], 'safe'],
            [['role_type'], 'exist', 'skipOnError' => true, 'targetClass' => PlanPeopleType::className(), 'targetAttribute' => ['role_type' => 'id']],
            [['idpeople'], 'exist', 'skipOnError' => true, 'targetClass' => People::className(), 'targetAttribute' => ['idpeople' => 'id']],
            [['idplan'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::className(), 'targetAttribute' => ['idplan' => 'id']],
            [['status', 'sequence'], 'default', 'value' => 1],
            [['role_type'], 'default', 'value' => $this->roleId],
            ['effective_date', 'checkDate'],
        ];
    }

    public function getPeople() {
        return $this->hasOne(People::className(), ['id' => 'idpeople'])
                        ->from(People::tableName() . ' servicingPeople');
    }

    public function getPeopleRole() {
        return $this->hasOne(PeopleRole::className(), ['idpeople' => 'idpeople']);
    }

    /**
     * 
     * @param type $idpeople
     * @param type $mode
     * @param type $filterType
     * @return boolean
     */
    public static function getAllClients($idpeople, $mode = "count", $filterType = "tree") {

        switch ($filterType) {
            case "self":
                $downlines = [$idpeople];
                break;
            case "tree":
                $downlines = People::getAllDownline($idpeople);
                break;
            case "downline":
                $downlines = People::getAllDownline($idpeople, 0, $filterType);
                break;
        }

        if (!isset($downlines)) {
            throw new \Exception($filterType);
        }

        $subQuery = self::find()->select('idplan')->where(['in', 'idpeople', $downlines])
                ->andWhere(['in', 'role_type', [PeopleRole::SERVICING]]);

        $query = self::find()->select([
                    new Expression('distinct idpeople')
                ])
                ->where(['in', 'role_type', [PeopleRole::HOLDER, PeopleRole::INSURED]])
                ->andWhere(['in', 'idplan', $subQuery])
                ->orderBy("idpeople ASC");

        if ($mode == "count") {
            $results = $query->count();
        } elseif ($mode == "array") {
            $results = $query->asArray()->all();
        } elseif ($mode == "idOnly") {
            $results = $query->column();
        } else {
            $results = $query->all();
        }
        return $results;
    }

    /**
     * 
     * @param int $consultantIdpeople
     * @param int $clientIdpeople
     * @param string $returnType "array", "model"
     * @param string $filterType
     * @return flat idplan array
     */
    public static function getAllClientPlans($consultantIdpeople, $clientIdpeople = null, $returnType = "array", $filterType = "tree") {
        $downlines = People::getAllDownline($consultantIdpeople, 3, $filterType);

        if (!$downlines)
            return FALSE;

        if ($returnType == "array") {
            $query = self::find()->select(new Expression('distinct plan_people.idplan as id'))
                    ->joinWith(['plan.planPeoples'])->alias("clientPP")
                    ->joinWith(['plan'])
                    ->where(['in', 'plan_people.idpeople', $downlines])
                    ->andWhere(['in', 'plan_people.role_type', [PeopleRole::SERVICING]])
                    ->andWhere(['plan_people.status' => 1])
                    ->andWhere(['<>', 'plan.status', Plan::DELETED]);

            if ($clientIdpeople) {
                $query->andWhere(['clientPP.idpeople' => $clientIdpeople]);
            }
            return $query->createCommand()->queryColumn();
        }
        if ($returnType == "model") {
            $query = self::find()
                    ->select('plan_people.idplan')
                    ->distinct()
                    ->joinWith(['plan.planPeoples'])->alias("clientPP")
                    ->where(['in', 'plan_people.idpeople', $downlines])
                    ->andWhere(['in', 'plan_people.role_type', [PeopleRole::SERVICING]])
                    ->andWhere(['plan_people.status' => 1])
                    ->andWhere(['<>', 'plan.status', Plan::DELETED]);

            if ($clientIdpeople) {
                $query->andWhere(['clientPP.idpeople' => $clientIdpeople]);
            }
            return $query->all();
        }
    }

    /**
     * 
     * @param type $consultantIdpeople
     * @param type $clientIdpeople
     * @return flat idplan array
     */
    public static function getSelfClientPlans($consultantIdpeople, $clientIdpeople = null, $returnType = "array") {

        if ($returnType == "array") {
            $query = self::find()->select(new Expression('distinct plan_people.idplan as id'))
                    ->joinWith(['plan.planPeoples'])->alias("clientPP")
                    ->joinWith(['plan'])
                    ->where(['plan_people.idpeople' => $consultantIdpeople])
                    ->andWhere(['in', 'plan_people.role_type', [PeopleRole::SERVICING]])
                    ->andWhere(['plan_people.status' => 1, 'plan.status' => 1]);

            if ($clientIdpeople) {
                $query->andWhere(['clientPP.idpeople' => $clientIdpeople]);
            }
            return $query->createCommand()->queryColumn();
        }
        if ($returnType == "model") {
            $query = self::find()
                    ->select('plan_people.idplan')
                    ->distinct()
                    ->joinWith(['plan.planPeoples'])->alias("clientPP")
                    ->where(['plan_people.idpeople' => $consultantIdpeople])
                    ->andWhere(['in', 'plan_people.role_type', [PeopleRole::SERVICING]])
                    ->andWhere(['plan_people.status' => 1]);

            if ($clientIdpeople) {
                $query->andWhere(['clientPP.idpeople' => $clientIdpeople]);
            }
            return $query->all();
        }
    }

    public static function checkPlanIsAccessible($idpeople, $idplan) {
        $query = self::find()
                ->select('plan_people.idplan')
                ->joinWith(['people'])
                ->where(['plan_people.idpeople' => $idpeople])
                ->andWhere(['plan_people.idplan' => $idplan])
                ->andWhere(['in', 'plan_people.role_type', [PeopleRole::SERVICING]])
                ->andWhere(['plan_people.status' => 1, 'servicingPeople.status' => 1, 'servicingPeople.active' => 1]);

        return $query->one();
    }

}
