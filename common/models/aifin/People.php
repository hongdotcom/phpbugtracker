<?php

namespace common\models\aifin;

use common\helpers\IfapHelper;
use common\models\WebLang;
use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class People extends \yii\db\ActiveRecord {

    public $relatedAudits = [
        'peopleGrades',
        'infoSets',
        'infoSets.addresses',
        'infoSets.idens',
        'infoSets.phones',
    ];

    const SCENARIO_CLIENT = 'client';
    const SCENARIO_CONSULTANT = 'consultant';
    const SCENARIO_PROVIDER_PEOPLE = 'provider_people';
    const SCENARIO_INTERNAL_STAFF = 'internal_staff';
    const SCENARIO_CONTACT_POINT = 'consultant_contact_point';
    const DRAFT = 9;
    const APPROVED = 1;
    const DELETED = 0;

    public $name;
    public $people_tag;
    public $age;
    public $mFullNameEng;
    public $mFullNameLocale;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'people';
    }

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['firstname_eng', 'lastname_eng', 'email_primary'], 'required', 'on' => [self::SCENARIO_INTERNAL_STAFF]],
            [['firstname_eng', 'lastname_eng'], 'required', 'on' => self::SCENARIO_PROVIDER_PEOPLE],
            [['nationality', 'dob', 'title_type'], 'required', 'on' => [self::SCENARIO_CLIENT]],
            [['bank_payment_currency'], 'required', 'on' => [self::SCENARIO_CONSULTANT]],
            [['lastname_eng', 'firstname_eng'], 'required', 'on' => [self::SCENARIO_CLIENT, self::SCENARIO_CONSULTANT], 'when' => function ($model) {
                    return $model->entity_type == 4; // individual
                }
//                , 'whenClient' => "function (attribute, value) {
//                        return $('#planform-payment_fqy').val() == '5';
//                    }"
            ],
            [['co_name'], 'required', 'on' => [self::SCENARIO_CLIENT, self::SCENARIO_CONSULTANT], 'when' => function ($model) {
                    return $model->entity_type == 1;
                }],
            ['email_primary', 'email'],
            [['active'], 'default', 'value' => 1],
            [['status'], 'default', 'value' => People::DRAFT],
            [['is_channel'], 'default', 'value' => 0],
            [['opt_out'], 'default', 'value' => 0],
            [['lang'], 'default', 'value' => 'eng'],
            ['dob', 'date', 'format' => 'php:Y-m-d'],
            [['lang', 'co_name', 'co_name_locale', 'firstname_eng', 'lastname_eng', 'firstname_locale', 'lastname_locale',
            'dob', 'title_type', 'occupation', 'industry', 'marital_status', 'salary', 'co_full_name', 'email_primary',
            'idpeople_master', 'commission_password', 'bank_name', 'bank_account_name', 'bank_address', 'bank_account_no',
            'bank_payment_method', 'bank_payment_currency', 'idregion'], 'default', 'value' => NULL],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['title_type', 'gender', 'occupation', 'industry', 'marital_status', 'salary', 'nationality',
            'opt_out', 'created_by', 'updated_by', 'deleted_by', 'active', 'status', 'entity_type',
            'idpeople_master', 'idfolder', 'bank_payment_method', 'bank_payment_currency', 'idregion',
            'pb_banker', 'consultant_grade', 'ideducation',
                ], 'integer'],
            [['remark'], 'string'],
            [['firstname_eng', 'lastname_eng', 'firstname_locale', 'lastname_locale', 'job_title', 'business_nature',
            'bank_account_no', 'ref_no'], 'string', 'max' => 60],
            [['co_name', 'co_full_name', 'bank_name', 'bank_account_name', 'employer'], 'string', 'max' => 100],
            [['co_name_locale', 'display_name', 'display_name_locale'], 'string', 'max' => 80],
            [['lang'], 'string', 'max' => 3],
            [['email_primary'], 'string', 'max' => 150],
            [['idpeople_old'], 'string', 'max' => 40],
            [['commission_password', 'bank_address'], 'string', 'max' => 255],
            [['nationality'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['nationality' => 'id']],
            ['people_tag', 'each', 'rule' => ['integer']],
        ];
    }

    public function fields() {
        $fields = parent::fields();
        $fields['mFullNameEng'] = $this->co_name . ' ' . $this->first_name . ' ' . $this->last_name;
        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCommPurchaseLedgers() {
        return $this->hasMany(CommPurchaseLedger::className(), ['idpeople' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNationalityCountry() {
        return $this->hasOne(Country::className(), ['id' => 'nationality']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion() {
        return $this->hasOne(Country::className(), ['id' => 'idregion']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeopleAddresses() {
        return $this->hasMany(PeopleAddress::className(), ['idpeople' => 'id'])
                        ->onCondition([PeopleAddress::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeopleEmails() {
        return $this->hasMany(PeopleEmail::className(), ['idpeople' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeopleIdentifications() {
        return $this->hasMany(PeopleIdentification::className(), ['idpeople' => 'id'])
                        ->onCondition([PeopleIdentification::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeoplePhones() {
        return $this->hasMany(PeoplePhone::className(), ['idpeople' => 'id'])
                        ->onCondition([PeoplePhone::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeopleRole() {
        return $this->hasOne(PeopleRole::className(), ['idpeople' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanPeoples() {
        return $this->hasMany(PlanPeople::className(), ['idpeople' => 'id'])
                        ->onCondition([PlanPeople::tableName() . '.status' => 1]);
    }

    public function getHolderPlanPeoples() {
        return $this->hasMany(PlanPeople::className(), ['idpeople' => 'id'])
                        ->onCondition([PlanPeople::tableName() . '.status' => 1, PlanPeople::tableName() . '.role_type' => PeopleRole::HOLDER]);
    }

    public function getInsuredPlanPeoples() {
        return $this->hasMany(PlanPeople::className(), ['idpeople' => 'id'])
                        ->onCondition([PlanPeople::tableName() . '.status' => 1, PlanPeople::tableName() . '.role_type' => PeopleRole::INSURED]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelationship() {
        return $this->hasOne(Relationship::className(), ['idpeople' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelationships() {
        return $this->hasMany(Relationship::className(), ['idpeople' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserPeoples() {
        return $this->hasMany(UserPeople::className(), ['idpeople' => 'id'])
                        ->from(UserPeople::tableName() . ' userPeoples')
                        ->onCondition(['userPeoples.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrimaryUser() {
        return $this->hasOne(User::className(), ['id' => 'iduser'])
                        ->from(User::tableName() . ' primaryUser')
                        ->onCondition(['primaryUser.status' => 1])
                        ->viaTable(UserPeople::tableName(), ['idpeople' => 'id'], function ($query) {
                            return $query->from(UserPeople::tableName() . ' primaryUserPeople')
                                    ->onCondition([
                                        'primaryUserPeople.primary_account' => 1,
                                        'primaryUserPeople.status' => 1
                            ]);
                        });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTitleTypeR() {
        return $this->hasOne(FormElement::className(), ['element_key' => 'title_type'])
                        ->from(FormElement::tableName() . ' titleType')
                        ->onCondition(['titleType.element_type' => 'title_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaritalType() {
        return $this->hasOne(FormElement::className(), ['element_key' => 'marital_status'])
                        ->from(FormElement::tableName() . ' maritalType')
                        ->onCondition(['maritalType.element_type' => 'marital_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSalaryR() {
        return $this->hasOne(Salary::className(), ['id' => 'salary']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIndustryR() {
        return $this->hasOne(Industry::className(), ['id' => 'industry']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOccupationR() {
        return $this->hasOne(Occupation::className(), ['id' => 'occupation']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLangR() {
        return $this->hasOne(FormElement::className(), ['element_key' => 'lang'])
                        ->from(FormElement::tableName() . ' langR')
                        ->onCondition(['langR.element_type' => 'lang']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntityType() {
        return $this->hasOne(EntityType::className(), ['id' => 'entity_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlans() {
        return $this->hasMany(Plan::className(), ['id' => 'idplan'])
                        ->onCondition(['<>', Plan::tableName() . '.status', Plan::DELETED])
                        ->distinct()
                        ->viaTable(PlanPeople::tableName(), ['idpeople' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
//    public function getPaymentCurrency() {
//        return $this->hasOne(Currency::className(), ['id' => 'bank_payment_currency']);
//    }

    public function getBankPaymentCurrency() {
        return $this->hasOne(Currency::className(), ['id' => 'bank_payment_currency'])
                        ->from("currency peopleCurrency")
                        ->onCondition(['peopleCurrency.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod() {
        return $this->hasOne(PaymentMethodType::className(), ['id' => 'bank_payment_method']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments() {
        return $this->hasMany(Document::className(), ['idpeople' => 'id'])
                        ->onCondition([Document::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeopleContacts() {
        return $this->hasMany(PeopleContact::className(), ['idpeople_parent' => 'id'])
                        ->from(PeopleContact::tableName() . ' peopleContacts')
                        ->onCondition(['peopleContacts.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeopleTag() {
        return $this->hasOne(PeopleTag::className(), ['id' => 'idtag'])
                        ->viaTable(PeopleTagRelationship::tableName(), ['idpeople' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeopleTagRelationship() {
        return $this->hasOne(PeopleTagRelationship::className(), ['idpeople' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrmRecordPeoples() {
        return $this->hasMany(CrmRecordPeople::className(), ['idpeople' => 'id']);
    }

    public function name($lang = NULL) {
        if ($lang == NULL) {
            $lang = Yii::$app->language;
        }
        if ($lang == 'cht' || $lang == 'chs' || $lang == "jap") {
            if ($this->entity_type == EntityType::INDIVIDUAL || $this->entity_type == EntityType::DIRECTOR) {
                $return = $this->lastname_locale . WebLang::sp($lang) . $this->firstname_locale;
            } else {
                $return = $this->co_name_locale;
            }
        } else { // eng
            if ($this->entity_type == EntityType::INDIVIDUAL || $this->entity_type == EntityType::DIRECTOR) {
                $return = $this->firstname_eng . WebLang::sp($lang) . $this->lastname_eng;
            } else {
                $return = $this->co_name;
            }
        }

        return ($return);
    }

    /**
     * Get People Name by id
     * @param $id
     * @return string
     */
    public static function getName($id) {
        /** @var People $people */
        $people = self::find()->where(['id' => $id, 'status' => 1])->one();

        return $people != NULL ? $people->name() : '';
    }

    /**
     * Should return array rather than object
     * @param $idpeople
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getTotalPU($idpeople) {
        $lang = Yii::$app->language;
        return self::find()
                        ->from(People::tableName() . ' e')
                        ->select([
                            'pu' => new Expression('sum(p.production_unit)'),
                            "pc.category_name_$lang as category_name"
                        ])
                        ->leftJoin("plan_people pp", "pp.idpeople=e.id")
                        ->leftJoin("plan p", "p.id=pp.idplan")
                        ->leftJoin("product d", "d.id=p.idproduct")
                        ->leftJoin("product_category pc", "pc.id=d.idproduct_cat")
                        ->where([
                            "e.id" => $idpeople,
                            "p.status" => 1,
                            "e.status" => 1,
                            "pp.status" => 1,
                            "pp.role_type" => 1,
                            "d.status" => 1,
                            "pc.status" => 1
                        ])
                        ->groupBy("pc.id")
                        ->orderBy(["pc.category_name_$lang" => SORT_ASC])
                        ->asArray()
                        ->all();
    }

    public static function userAccountEnabled($idpeople) {
        return self::find()
                        ->select(["up.iduser"])
                        ->from(People::tableName() . ' e')
                        ->leftJoin("user_people up", "up.idpeople=e.id")
                        ->leftJoin("user u", "u.id=up.iduser")
                        ->where([
                            "e.id" => $idpeople,
                            "e.status" => 1,
                            "up.status" => 1,
                            "u.status" => 1,
                        ])
                        ->scalar();
    }

    public function peopleName() {
        return 'CASE
                WHEN entity_type = 1 THEN co_name
                WHEN entity_type = 2 THEN co_name
                ELSE CONCAT(COALESCE(lastname_eng,"")," ",COALESCE(firstname_eng,"")) 
                END';
    }

    public function softDelete() {
        if (count($this->planPeoples) > 0)
            return FALSE;
        else {
            $result = parent::softDelete();

            if (!$result) {
                return FALSE;
            }

            // Delete people_role
            PeopleRole::deleteAll(['status' => 1, 'idpeople' => $this->id]);

            // Delete people_info_set
            PeopleInfoSet::softDeleteAll(['idpeople' => $this->id, 'status' => 1]);

            // Delete people_identification
            PeopleIdentification::softDeleteAll(['idpeople' => $this->id, 'status' => 1]);

            // Delete people_phone
            PeoplePhone::softDeleteAll(['idpeople' => $this->id, 'status' => 1]);

            // Delete people_address
            PeopleAddress::softDeleteAll(['idpeople' => $this->id, 'status' => 1]);

            // Delete user_people
            UserPeople::deleteAll(['status' => 1, 'idpeople' => $this->id]);

            // Delete people_grade
            PeopleGrade::deleteAll(['idpeople' => $this->id, 'status' => 1]);

            // Delete people_grade_tx
            PeopleGradeTx::deleteAll(['idpeople' => $this->id, 'status' => 1]);

            return TRUE;
        }
    }

    public static function getBDArray() {
        $BDs = PeopleRole::getAllConsultant(2);
        $return = array();

        foreach ((array) $BDs as $item) {
            $return[] = array(
                'id' => $item['id'],
                'itemname' => (Yii::$app->language == "cht" || Yii::$app->language == "chs") ? $item['name_locale'] : $item['name_eng']
            );
        }
        return $return;
    }

    public static function getBrokerKArray() {
        $Brokers = PeopleRole::getAllConsultant(3);
        $return = array();

        foreach ((array) $Brokers as $item) {
            $return[] = array(
                'id' => $item['id'],
                'itemname' => (Yii::$app->language == "cht" || Yii::$app->language == "chs") ? $item['name_locale'] : $item['name_eng']
            );
        }
        return $return;
    }

    public static function getConsultantArray($level) {
        $consultants = PeopleRole::getAllConsultant($level);
        $return = array();

        foreach ((array) $consultants as $item) {
            $return[] = array(
                'id' => $item['id'],
                'itemname' => (Yii::$app->language == "cht" || Yii::$app->language == "chs") ? $item['name_locale'] : $item['name_eng']
            );
        }
        return $return;
    }

    public static function findModel($idpeople, $peopleRoleType) {
        $model = People::find()
                ->joinWith([
                    'infoSets.addresses',
                    'infoSets.phones',
                    'infoSets.idens',
                    /* 'peopleAddresses',
                      'peoplePhones',
                      'peopleIdentifications', */
                    'peopleRole' => function ($query) use ($peopleRoleType) {
                        switch ($peopleRoleType) {
                            case IfapHelper::PT_AGENT10:
                                return $query->where(['BETWEEN', 'idpeople_role_type', IfapHelper::PT_AGENT1, IfapHelper::PT_AGENT10]);
                                break;
                            case IfapHelper::PT_CLIENT:
                                return $query->where(['idpeople_role_type' => IfapHelper::PT_CLIENT]);
                                break;
                            default:
                                return $query;
                                break;
                        }
                    },
                ])
                ->where([People::tableName() . '.id' => $idpeople])
                ->andWhere(['<>', People::tableName() . '.status', People::DELETED])
                ->one();
        return $model;
    }

    /**
     * Get downlines of People with ARA records
     * @param int $idpeople
     * @param type $returnType 0=PeopleModel, 1=IN string, 2=selectbox format, 3=flat idpeople array
     * @param string $filterType
     * @return mixed
     */
    public static function getAllDownline($idpeople, $returnType = 0, $filterType = "tree", $directDownlineOnly = FALSE) {

        $idpeople = (int) $idpeople;

        if ($idpeople <= 0 || !$idpeople) {
//            throw new \Exception("Invalid idpeople");
            return null;
        }
        // Find out what Agent Level he is
        $people_type = PeopleRole::find()
                ->joinWith(['people'])
                ->select(['idpeople_role_type'])
                ->where([
                    'people_role.status' => 1,
                    'idpeople' => $idpeople,
                ])
                ->scalar();

        if ($people_type > 0 && $people_type <= 10) {
            $target_column = "agent" . $people_type . "_code";

            $query = AgencyRelationshipAdvance::find()
                    ->from(AgencyRelationshipAdvance::tableName() . ' me')
                    ->joinWith(['people'])
                    ->where([
                        $target_column => $idpeople,
                        "people.status" => 1,
                        "people.active" => 1
                    ])
                    ->orderBy([
                "me.people_role_type" => SORT_ASC,
                "people.lastname_eng" => SORT_ASC,
                "people.co_name" => SORT_ASC,
            ]);
        } else { // if not role 1-10, returns nothing
            Yii::error("Invalid people_type $people_type");
            return null;
        }

        // $returnType 0 returns PeopleModel
        if ($returnType <> 0) {
            $query->select("idpeople");
        }

        // if need to exclude the upline
        switch ($filterType) {
            case "self":
                $query->andWhere(['=', 'me.idpeople', $idpeople]);
                break;
            case "tree":
                break;
            case "downline":
                $query->andWhere(['<>', 'me.idpeople', $idpeople]);
                break;
        }

        // return directDownline or whole tree?
        if ($people_type < 10) {
            $people_type++;
        }

        if ($directDownlineOnly) {
            $query->andWhere(['people_role_type' => $people_type]);
        }

        if ($returnType == 1) {
            $result = $query->asArray()->all();

            if ($result) {
                if (count($result) > 0) {
                    $downline_string = "(";

                    foreach ((array) $result as $item) {
                        $downline_string .= "'" . $item['idpeople'] . "',";
                    }

                    $downline_string = trim($downline_string, ",");
                    $downline_string .= ")";
                }
                return $downline_string;
            } else {
                return "";
            }
        } elseif ($returnType == 2) { // for selectbox
            $result = $query->asArray()->all();

            $return = array();

            if ($result) {
                if (count($result) > 0) {
                    foreach ((array) $result as $item) {
                        // TODO: remove this sql
                        $people = People::findActiveOne([$item['idpeople']]);
                        $return[] = array(
                            "id" => $item['idpeople'],
                            "active" => $item['active'],
                            "itemname" => $people->name() . " (Level " . $item['people_role_type'] . ", " . $item['idpeople'] . ")",
                        );
                    }
                }
            }
            return $return;
        } elseif ($returnType == 3) { // simple array
            $result = $query->createCommand()->queryColumn();
            return $result;
        } else {
            $result = $query->all();
            return $result;
        }
    }

    public static function getAllWithinLevel($levelFrom, $levelTo) {
        if (Yii::$app->language == "eng") {
            $cols = array("p.id",
                "p.firstname_eng as firstname",
                "p.lastname_eng as lastname",
                "p.co_name",
                "p.entity_type",
                "p2.firstname_eng as upline_firstname",
                "p2.lastname_eng as upline_lastname",
                "p2.co_name as upline_co_name",
                "p2.entity_type as upline_entity_type",
                "p.active",
                new Expression("concat_ws(' ', p.lastname_locale, p.firstname_locale, p.co_name_locale) as people_name"),
                new Expression("concat_ws(' ', p2.lastname_locale, p2.firstname_locale, p2.co_name_locale) as upline_name"),
            );
        } else {
            $cols = array("p.id",
                "p.firstname_eng as firstname",
                "p.lastname_eng as lastname",
                "p.co_name",
                "p.entity_type",
                "p.entity_type",
                "p2.firstname_locale as upline_firstname",
                "p2.lastname_locale as upline_lastname",
                "p2.co_name_locale as upline_co_name",
                "p2.entity_type as upline_entity_type",
                "p.active",
                new Expression("concat_ws(' ', p.lastname_locale, p.firstname_locale, p.co_name_locale) as people_name"),
                new Expression("concat_ws(' ', p2.lastname_locale, p2.firstname_locale, p2.co_name_locale) as upline_name"),
            );
        }

        $results = self::find()
                ->select($cols)
                ->from(People::tableName() . ' p')
                ->innerJoin("people_role r", "p.id=r.idpeople")
                ->innerJoin("relationship s", "s.idpeople=p.id")
                ->innerJoin("people p2", "p2.id=s.idparent")
                ->where([
                    "p.status" => 1,
                    "s.status" => 1,
                ])
                ->andWhere(['>=', "r.idpeople_role_type", $levelFrom])
                ->andWhere(['<=', "r.idpeople_role_type", $levelTo])
                ->orderBy([
                    "p.lastname_eng" => SORT_ASC,
                    "p.firstname_eng" => SORT_ASC,
                    "p.co_name" => SORT_ASC,
                ])
                ->asArray()
                ->all();

        $converted_results = array();

        foreach ($results as $result) {
            $idpeople_text = " (" . $result['id'] . ")";

// Upline name
            if ($result['upline_entity_type'] == 0) {
                if (trim($result['upline_firstname']) !== "" or trim($result['upline_lastname']) !== "") {
                    $upline_name = $result['upline_lastname'] . " " . $result['upline_firstname'];
                } else {
                    $upline_name = $result['upline_co_name'];
                }
            } else {
                if (trim($result['upline_co_name']) !== "") {
                    $upline_name = $result['upline_co_name'];
                } else {
                    $upline_name = $result['upline_lastname'] . " " . $result['upline_firstname'];
                }
            }

// Consultant name
            if ($result['entity_type'] == 0) {
                if (trim($result['firstname']) !== "" or trim($result['lastname']) !== "") {
                    array_push(
                            $converted_results, array(
                        "id" => $result['id'],
                        "itemname" => $result['lastname'] . " " . $result['firstname'] . $idpeople_text . " [ $upline_name ]",
                        "active" => $result['active'],
                        "people_name" => $result['people_name']
                            )
                    );
                } else {
                    array_push($converted_results, array(
                        "id" => $result['id'],
                        "itemname" => $result['co_name'] . " " . $idpeople_text . " [ $upline_name ]",
                        "active" => $result['active'],
                        "people_name" => $result['people_name']
                    ));
                }
            } else {
                if (trim($result['co_name']) !== "") {
                    array_push($converted_results, array(
                        "id" => $result['id'],
                        "itemname" => $result['co_name'] . " " . $idpeople_text . " [ $upline_name ]",
                        "active" => $result['active'],
                        "people_name" => $result['people_name']
                    ));
                } else {
                    array_push($converted_results, array(
                        "id" => $result['id'],
                        "itemname" => $result['lastname'] . " " . $result['firstname'] . $idpeople_text . " [ $upline_name ]",
                        "active" => $result['active'],
                        "people_name" => $result['people_name']
                    ));
                }
            }
        }
        return $converted_results;
    }

    public static function getAllUpline($idpeople, $returnType = 0) {
        // Find out what Agent Level he is
        // Find out what Agent Level he is
        $people_type = PeopleRole::find()
                ->select(['idpeople_role_type'])
                ->where([
                    'status' => 1,
                    'idpeople' => $idpeople,
                ])
                ->scalar();

        if ($people_type > 0 and $people_type <= 10) {
            if ($people_type >= 2) {
                $target_column = "agent" . $people_type . "_code";

                $target_people_type = $people_type;
                $upline_column = "agent" . $people_type . "_code";
            } else {
                return false; // level 1 should hv no upline
            }

            $result = AgencyRelationshipAdvance::find()
                    ->from(AgencyRelationshipAdvance::tableName() . ' me')
                    ->leftJoin("people e", "e.id=me.idpeople AND e.active=1 AND e.status=1")
                    ->leftJoin("people e0", "e0.id=me.agent0_code AND e0.active=1 AND e0.status=1")
                    ->leftJoin("people e1", "e1.id=me.agent1_code AND e1.active=1 AND e1.status=1")
                    ->leftJoin("people e2", "e2.id=me.agent2_code AND e2.active=1 AND e2.status=1")
                    ->leftJoin("people e3", "e3.id=me.agent3_code AND e3.active=1 AND e3.status=1")
                    ->leftJoin("people e4", "e4.id=me.agent4_code AND e4.active=1 AND e4.status=1")
                    ->leftJoin("people e5", "e5.id=me.agent5_code AND e5.active=1 AND e5.status=1")
                    ->leftJoin("people e6", "e6.id=me.agent6_code AND e6.active=1 AND e6.status=1")
                    ->leftJoin("people e7", "e7.id=me.agent7_code AND e7.active=1 AND e7.status=1")
                    ->leftJoin("people e8", "e8.id=me.agent8_code AND e8.active=1 AND e8.status=1")
                    ->leftJoin("people e9", "e9.id=me.agent9_code AND e9.active=1 AND e9.status=1")
                    ->leftJoin("people e10", "e10.id=me.agent10_code AND e10.active=1 AND e10.status=1")
                    ->where([
                        $target_column => $idpeople,
                        "people_role_type" => $target_people_type,
                    ])
                    ->orderBy([
                        "me.people_role_type" => SORT_ASC,
                        "e.lastname_eng" => SORT_ASC,
                        "e.co_name" => SORT_ASC,
                    ])
//                    ->asArray()
                    ->all();

            if ($returnType == 1) {
                $upline_string = "";

                if (count($result) > 0) {
                    $upline_string = "(";

                    foreach ((array) $result as $item) {
                        $upline_string .= "'" . $item->idpeople . "',";
                    }

                    $upline_string = trim($upline_string, ",");
                    $upline_string .= ")";
                } else {
                    
                }
                return $upline_string;
            } else {
                return $result;
            }
        } else {
            return null;
        }
    }

    public function ifapValidate() {
        if ($this->entity_type == IfapHelper::ENTITY_INDIVIDUAL) {
            if (empty($this->firstname_eng) || empty($this->lastname_eng)) {
                $this->addError('app', "Invalid First Name and Last Name for Individual entity type");
            }
        }
        if ($this->entity_type == IfapHelper::ENTITY_COMPANY) {
            if (empty($this->co_name)) {
                $this->addError('app', "Invalid Company Name for Company entity type");
            }
        }
        return !$this->hasErrors();
    }

    /**
     * @param $idpeople
     * @param null|array $cols
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getConsultantActivePlans($idpeople, $cols = null) {
        $query = new Query();

        $query->from(self::tableName() . ' e')
                ->innerJoin("plan_people pp", "pp.idpeople=$idpeople AND pp.role_type=" . IfapHelper::WRITING . " AND pp.status=1")
                ->innerJoin("plan p", "p.id=pp.idplan AND p.status=1")
                ->where(['e.id' => $idpeople]);

        if ($cols != NULL)
            $plans = $query->select($cols)->createCommand()->queryAll();
        else
            $plans = $query->createCommand()->queryAll();

        return $plans;
    }

    public function updateCommissionPassword($newPassword) {
        $this->commission_password = $newPassword;
        return $this->save();
    }

    public function deactivate() {
        $this->active = 0;
        return $this->save();
    }

    public function activate() {
        $this->active = 1;
        return $this->save();
    }

    public function updatePeopleRole($peopleRoleType) {
        $peopleRole = $this->peopleRole;

        if ($peopleRole == NULL)
            $peopleRole = new PeopleRole();

        $peopleRole->idpeople = $this->id;
        $peopleRole->idpeople_role_type = $peopleRoleType;

        return $peopleRole->save();
    }

    /**
     * Save People ABC into set and also handle different delete conditions
     * @return bool
     */
    public function saveABC() {

        $oldInfoSetIDs = ArrayHelper::map($this->infoSets, 'id', 'id');
        $infoSetModels = DynamicForms::createMultiple(PeopleInfoSet::classname(), $this->infoSets);
        Model::loadMultiple($infoSetModels, Yii::$app->request->post());

        if (Model::validateMultiple($infoSetModels)) {
//dd($infoSetModels);
            $deletedInfoSetIDs = array_diff($oldInfoSetIDs, array_filter(ArrayHelper::map($infoSetModels, 'id', 'id')));

            if (!empty($deletedInfoSetIDs)) {
                PeopleInfoSet::softDeleteAll(['IN', 'id', $deletedInfoSetIDs]);
                PeopleIdentification::softDeleteAll(['IN', 'idset', $deletedInfoSetIDs]);
                PeoplePhone::softDeleteAll(['IN', 'idset', $deletedInfoSetIDs]);
                PeopleAddress::softDeleteAll(['IN', 'idset', $deletedInfoSetIDs]);
            }

            /**
             * Handle ABC by set
             * @var PeopleInfoSet $infoSetModel
             */
            foreach ($infoSetModels as $index => $infoSetModel) {
                $infoSetModel->idpeople = $this->id;
                $idenData['PeopleIdentification'] = isset(Yii::$app->request->post()['PeopleIdentification'][$index]) ? Yii::$app->request->post()['PeopleIdentification'][$index] : [];
                $phoneData['PeoplePhone'] = isset(Yii::$app->request->post()['PeoplePhone'][$index]) ? Yii::$app->request->post()['PeoplePhone'][$index] : [];
                $addressData['PeopleAddress'] = isset(Yii::$app->request->post()['PeopleAddress'][$index]) ? Yii::$app->request->post()['PeopleAddress'][$index] : [];

                if (empty($idenData['PeopleIdentification']) && empty($phoneData['PeoplePhone']) && empty($addressData['PeopleAddress'])) {
                    Yii::error("Iden, phone, address ALL empty.  Deleted infoset");
                    $infoSetModel->softDelete();
                } else {
                    if (!$infoSetModel->save()) {
                        $this->addError("Error saving infoSet $index");
                        return FALSE;
                    } else {
                        Yii::error("Infoset saved");
                    }
                }

                $oldIdenIDs = ArrayHelper::map($infoSetModel->idens, 'id', 'id');
                $idModels = DynamicForms::createMultiple(PeopleIdentification::classname(), $infoSetModel->idens, $idenData);

                Model::loadMultiple($idModels, $idenData);

                if (Model::validateMultiple($idModels)) {
                    $deletedIdenIDs = array_diff($oldIdenIDs, array_filter(ArrayHelper::map($idModels, 'id', 'id')));

                    if (!empty($deletedIdenIDs)) {
                        PeopleIdentification::softDeleteAll(['id' => $deletedIdenIDs]);
                    }

                    /** @var PeopleIdentification[] $idModels */
                    foreach ($idModels as $idModel) {
                        if ($idModel->isNewRecord) {
                            $idModel->idpeople = $this->id;
                            $idModel->idset = $infoSetModel->id;
                        }

                        if (!$idModel->save()) {
                            return FALSE;
                        }
                    }
                } else {
                    Yii::error("No identification found");
                    return FALSE;
                }

                $oldPhoneIDs = ArrayHelper::map($infoSetModel->phones, 'id', 'id');
                $phoneModels = DynamicForms::createMultiple(PeoplePhone::classname(), $infoSetModel->phones, $phoneData);
                Model::loadMultiple($phoneModels, $phoneData);

                if (Model::validateMultiple($phoneModels)) {
                    $deletedPhoneIDs = array_diff($oldPhoneIDs, array_filter(ArrayHelper::map($phoneModels, 'id', 'id')));
                    if (!empty($deletedPhoneIDs)) {
                        PeoplePhone::softDeleteAll(['id' => $deletedPhoneIDs]);
                    }

                    /** @var PeoplePhone[] $phoneModels */
                    foreach ($phoneModels as $phoneModel) {
                        if ($phoneModel->isNewRecord) {
                            $phoneModel->idpeople = $this->id;
                            $phoneModel->idset = $infoSetModel->id;
                        }

                        if (!$phoneModel->save()) {
                            return FALSE;
                        }
                    }
                } else {
                    return FALSE;
                }

                $oldAddressIDs = ArrayHelper::map($infoSetModel->addresses, 'id', 'id');
                $addressModels = DynamicForms::createMultiple(PeopleAddress::classname(), $infoSetModel->addresses, $addressData);
                Model::loadMultiple($addressModels, $addressData);

                if (Model::validateMultiple($addressModels)) {
                    $deletedAddressIDs = array_diff($oldAddressIDs, array_filter(ArrayHelper::map($addressModels, 'id', 'id')));
                    if (!empty($deletedAddressIDs)) {
                        PeopleAddress::softDeleteAll(['id' => $deletedAddressIDs]);
                    }

                    /** @var PeopleAddress[] $addressModels */
                    foreach ($addressModels as $addressModel) {
                        if ($addressModel->isNewRecord) {
                            $addressModel->idpeople = $this->id;
                            $addressModel->idset = $infoSetModel->id;
                        }

                        if (!$addressModel->save()) {
                            return FALSE;
                        }
                    }
                } else {
                    return FALSE;
                }
            }
        } else {
            $this->addError('app', $msg = 'saveABC validation failed');
            Yii:error($msg);
            return FALSE;
        }
        return TRUE;
    }

    public function addRole($peopleRoleType) {

        $peopleRole = new PeopleRole();
        $peopleRole->idpeople = $this->id;
        $peopleRole->idpeople_role_type = $peopleRoleType;

        if (!$peopleRole->save()) {
            $this->addError('app', 'Save peopleRole failed');
//            dd($this->getErrors());
            return FALSE;
        }
        return TRUE;
    }

    public function addProviderPeople($idprovider) {
        $providerPeople = new ProviderPeople();
        $providerPeople->idprovider = $idprovider;
        $providerPeople->idpeople = $this->id;
        return $providerPeople->save();
    }

    public function getInfoSets() {
        return $this->hasMany(PeopleInfoSet::className(), ['idpeople' => 'id'])
                        ->onCondition([PeopleInfoSet::tableName() . '.status' => 1]);
    }

    public function getInfoSetDefault() {
        return $this->hasOne(PeopleInfoSet::className(), ['idpeople' => 'id'])
                        ->onCondition([PeopleInfoSet::tableName() . '.status' => 1, 'default' => 1]);
    }

    public function getInfoSetList() {
        $infoSets = $this->infoSets;
        $list = [];
        if (!empty($infoSets)) {

            /** @var PeopleInfoSet $defaultSet */
            $defaultSet = PeopleInfoSet::getDefault($this->id, TRUE);

            if ($defaultSet->checkABC()) {
                $list[PeopleInfoSet::getDefault($this->id)->id] = WebLang::$t['use_default'];
            }

            foreach ($infoSets as $infoSet) {
                if (!$infoSet->default) {
                    if ($infoSet->checkABC())
                        $list[$infoSet->id] = $infoSet->id;
                }
            }
        }
        return $list;
    }

    /**
     * Get all the peoples who they are clients of any of the downlines of the login user
     * The login user can be assistant or service desk, check the user_people table
     * @return array
     */
    public static function getPeopleIdsByHierarchy() {
        $downlines = User::getDownlinePeopleIds();

        if (!empty($downlines)) {
            $planIds = PlanPeople::find()
                    ->distinct()
                    ->select(['idplan'])
                    ->where(['IN', 'idpeople', $downlines])
                    ->andWhere(['role_type' => 2])
                    ->column();

            if (!empty($planIds)) {
                return PlanPeople::find()
                                ->distinct()
                                ->select(['idpeople'])
                                ->where(['IN', 'idplan', $planIds])
                                ->andWhere(['role_type' => 1])
                                ->column();
            }
        }
        return [];
    }

    /**
     * Create / Update People
     * @param $peopleRoleType
     * @param $idprovider
     * @return bool
     */
    public function process($peopleRoleType, $idprovider) {

        /*
         * 2018-6-2 Internal user & consultant always approved
         */
        if (in_array($peopleRoleType, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, IfapHelper::PT_STAFF])) {
            $this->status = People::APPROVED;
        }
        // handle gender
        if (in_array($this->title_type, [2, 3])) {
            $this->gender = 2;
        } elseif ($this->title_type == 1) {
            $this->gender = 1;
        } else {
            $this->gender = 0;
        }

        // Save the record status before saving
        $isNew = $this->isNewRecord;

        if (!$this->save()) {
            $this->addError('app', 'Save people document folder failed');
            return FALSE;
        }

        if ($isNew) {
            if ($peopleRoleType != NULL) {
                if (!$this->addRole($peopleRoleType)) {
                    return FALSE;
                }
                if ($peopleRoleType == IfapHelper::PT_STAFF)
                    if (!User::create($this->id, $this->email_primary, 4, 1)) {
                        return FALSE;
                    }
                if ($peopleRoleType > IfapHelper::PT_AGENT1 && $peopleRoleType <= IfapHelper::PT_AGENT10) {
                    // create folder, peopleGrades
                    $documentFolder = new DocumentFolder();
                    $documentFolder->folder_name_eng = WebLang::t("people_folder", 'eng');
                    $documentFolder->folder_name_cht = WebLang::t("people_folder", 'cht');
                    $documentFolder->folder_name_chs = WebLang::t("people_folder", 'chs');
                    $documentFolder->folder_name_jap = WebLang::t("people_folder", 'jap');
                    $documentFolder->idpeople_owner = $this->id;
                    $documentFolder->fs_folder_name = $this->id;
                    $documentFolder->is_public = 0;
                    $documentFolder->status = 1;

                    if (!$documentFolder->save()) {
                        Yii::error("error saving documentFolder");
                        return FALSE;
                    }
                    // update people root folder to people table
                    $this->idfolder = $documentFolder->id;

                    $gradeCampaign = GradeCampaign::findBaseCampaign();

                    if ($gradeCampaign) {
                        $idgrade = Grade::findProposedGrade(0);

                        if (!$idgrade) {
                            Yii::error("error finding default idgrade for gradeCampaign {$gradeCampaign->id}");
                            return FALSE;
                        }

                        $peopleGrade = new PeopleGrade;
                        $peopleGrade->idpeople = $this->id;
                        $peopleGrade->status = 1;
                        $peopleGrade->idgrade = $idgrade->id;
                        $peopleGrade->total_score = 0;
                        $peopleGrade->effective_date = date(IfapHelper::DATEFORMAT);

                        if (!$peopleGrade->save()) {
                            Yii::error("error saving peopleGrade" . var_export($peopleGrade->getErrors(), true));
                            return FALSE;
                        }
                    }
                }
            } else {
                $this->addError('app', 'Invalid peopleRoleType');
                return FALSE;
            }

            if ($idprovider != NULL && !$this->addProviderPeople($idprovider))
                return FALSE;
        }

        if (!$this->saveABC()) {
            return FALSE;
        }
        return TRUE;
    }

    public static function getTimeline($idpeople, $arrPeopleRoles) {

        $lang = Yii::$app->language;
        $arrIdplan = null;

        $pcResults = [];
        $crmPlanResults = [];
        $crmPeopleResults = [];

        // PLANS
        $planResults = Plan::find()
                        ->select([
                            'plan.id as eventid',
                            "plan.commencement_date as eventdate",
                            new Expression("'plan' as eventtype"),
                            new Expression("'" . WebLang::t('in_force') . "' as eventname"),
                        ])->where([
                            "plan_people.idpeople" => $idpeople,
                            "plan_people.status" => 1,
                            "plan.status" => 1])
                        ->andWhere(["in", "plan_people.role_type", $arrPeopleRoles])
                        ->andWhere("commencement_date is not null")
                        ->joinWith(['planPeoples'])
                        ->orderBy('plan.commencement_date DESC')
                        ->createCommand()->queryAll();

        foreach ((array) $planResults as $plan) {
            $arrIdplan[] = $plan['eventid'];
        }

        if ($arrIdplan) {
            // PLAN CHANGES
            $pcResults = PlanChange::find()
                            ->select([
                                new Expression('plan_change.id as eventid'),
                                new Expression("plan_change.commencement_date as eventdate"),
                                new Expression("'planchange' as eventtype"),
                                new Expression("'" . WebLang::t('in_force') . "' as eventname")
                            ])->where([
                                "holderpp.idpeople" => $idpeople,
                                "holderpp.status" => 1,
                                "plan.status" => 1])
                            ->andWhere(["in", "holderpp.role_type", $arrPeopleRoles])
                            ->andWhere("plan_change.commencement_date is not null")
                            ->joinWith(['plan', 'plan.holders'])
                            ->orderBy('plan_change.commencement_date DESC')
                            ->createCommand()->queryAll();
        }

        // PEOPLE CRM Records
        $crmPeopleResults = CrmRecord::find()
                        ->select([
                            new Expression('distinct crm_record.id as eventid'),
                            new Expression("date(crm_record.created_at) as eventdate"),
                            new Expression("'crm_people' as eventtype"),
                            new Expression("concat_ws(' ', '" . WebLang::t('crm') . " - ', crm_record_type.crm_record_type_name_$lang) as eventname"),
                        ])->where([
                            "crm_record_relation.idpeople" => $idpeople,
                            "crm_record_relation.status" => 1,
                            "crm_record.status" => 1,
                        ])->joinWith(['crmRecordRelations', 'crmRecordType'])
                        ->orderBy('crm_record.created_at DESC')
                        ->createCommand()->queryAll();

        $results = array_merge($planResults, $pcResults, $crmPeopleResults);

        $ord = array();
        foreach ((array) $results as $key => $value) {
            $ord[] = strtotime($value['eventdate']);
        }
        array_multisort($ord, SORT_DESC, $results);

        return $results;
    }

    public static function findPeopleGradeName($idpeople) {
        return self::find()
                        ->select([Yii::$app->language])
                        ->joinWith(['peopleGrade.grade'])
                        ->where(['people.id' => $idpeople, 'people.status' => 1, 'people_grade.status' => 1])
                        ->scalar();
    }

    public function getPeopleGrade() {
        return $this->hasOne(PeopleGrade::className(), ['idpeople' => 'id'])
                        ->onCondition([
//                            PeopleGrade::tableName() . '.idgrade_cat' => GradeCategory::DEFAULT_CAT,
                            PeopleGrade::tableName() . '.status' => 1,
        ]);
    }

    public function getPeopleGrades() {
        return $this->hasMany(PeopleGrade::className(), ['idpeople' => 'id'])
                        ->onCondition([PeopleGrade::tableName() . '.status' => 1]);
    }

    public function getPeopleGradeTxs() {
        return $this->hasMany(PeopleGradeTx::className(), ['idpeople' => 'id'])
                        ->onCondition([PeopleGradeTx::tableName() . '.status' => 1]);
    }

    public static function totalGradeScore($idpeople) {
        return self::find()->select([
                            new Expression('sum(score)'),
                        ])->where(['idpeople' => $idpeople, PeopleGradeTx::tableName() . '.status' => 1])
                        ->joinWith(['peopleGradeTxs'])
                        ->scalar();
    }

    public function getCrmRecordRelations() {
        return $this->hasMany(CrmRecordRelation::className(), ['idpeople' => 'id'])
                        ->onCondition([CrmRecordRelation::tableName() . '.status' => 1]);
    }

    public function getConsultantGrade() {
        return $this->hasOne(Grade::className(), ['id' => 'consultant_grade']);
    }

    /**
     * 
     * @param type $arrIdpeopleHierarchy
     * @param type $maxDays
     * @return array
     */
    public static function findByDob($arrIdpeopleHierarchy, $maxDays, $lang) {

        return self::find()
                        ->select([
                            'people.id',
                            "grade.$lang as consultant_grade",
                            new Expression('concat_ws(" ", co_name, firstname_eng, lastname_eng) as fullname'),
                            new Expression('concat_ws(" ", co_name_locale, lastname_locale, firstname_locale) as fullname_locale'),
                            new Expression("TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age")
                        ])
                        ->joinWith([
                            'consultantGrade',
                            'peopleRole.peopleRoleType',
                                ], true, "inner join")
                        ->where("DATE_FORMAT(dob, '%m-%d') BETWEEN DATE_FORMAT(NOW(), '%m-%d') AND DATE_FORMAT(DATE_ADD(NOW(), INTERVAL $maxDays DAY), '%m-%d')")
                        ->andWhere(['people.status' => 1, 'people.active' => 1, 'people_role_type.id' => PeopleRoleType::CLIENT])
                        ->andWhere(['in', 'people.id', $arrIdpeopleHierarchy])
                        ->asArray()
                        ->all();
    }

    public function getPeopleProvider() {
        return $this->hasOne(ProviderPeople::className(), ['idpeople' => 'id'])
                        ->onCondition([ProviderPeople::tableName() . '.status' => 1]);
    }

    public static function getAllClientPlans($idpeople, $where = NULL) {
        $lang = Yii::$app->language;

        // get all downlines
        $downlines = self::getAllDownline($idpeople, 1);

        $query = self::find()
                ->select([
                    new Expression("distinct p.id"),
                    "p.*",
                    "ppt.pp_type_name_$lang as role_type_name",
                    "d.product_name_$lang as product_name",
                    "s.status_name_$lang as plan_status_name",
                    "c.currency_name_$lang as plan_currency_name",
                    "v.provider_name_$lang as provider_name",
                    "pc.category_name_$lang as category_name",
                    new Expression("GREATEST(IFNULL(p.regular_premium,0), IFNULL(p.investment_amount,0), IFNULL(p.target_premium,0)) as amount")
                ])
                ->from(self::tableName() . ' e')
                ->leftJoin("plan_people pp", "pp.idpeople=e.id")
                ->leftJoin("plan_people_type ppt", "ppt.id=pp.role_type")
                ->leftJoin("plan p", "p.id=pp.idplan")
                ->leftJoin("product d", "d.id=p.idproduct")
                ->leftJoin("status_detail s", "s.id=p.plan_status and s.idproduct_cat=d.idproduct_cat")
                ->leftJoin("currency c", "c.id=p.plan_currency")
                ->leftJoin("provider v", "v.id=p.idprovider")
                ->leftJoin("product_category pc", "pc.id=d.idproduct_cat")
                ->leftJoin("status_master sm", "sm.id=s.idstatus_master")
                ->where([
                    "pp.role_type" => 3,
                    "p.status" => 1
                ])
                ->andWhere(['IN', "sm.id", [1, 2]]);

        if ($where <> null) {
            $query->andWhere($where);
        }

        $query->orderBy([
            "app_rece_date" => SORT_DESC,
            "p.id" => "desc"
        ]);

        if ($downlines != false) { // if no downline, still need to retrieve her OWN plans
            $query->andWhere("e.id IN $downlines");
        } else {
            $query->andWhere(["e.id" => $idpeople]);
        }

        return $query->limit(300)->asArray()->all();
    }

    public function getAgencyRelationshipAdvance() {
        return $this->hasOne(AgencyRelationshipAdvance::className(), ['idpeople' => 'id']);
    }

    public function getLicenses() {
        return $this->hasMany(License::className(), ['id' => 'idlicense'])
                        ->viaTable(PeopleLicense::tableName(), ['idpeople' => 'id'])
                        ->onCondition([License::tableName() . '.status' => 1]);
    }

    public function getPeopleLicenses() {
        return $this->hasMany(PeopleLicense::className(), ['idpeople' => 'id'])
                        ->onCondition([PeopleLicense::tableName() . '.status' => 1]);
    }

    public function createDocumentFolder() {
        if (!$this->idfolder) {
            $docFolder = new DocumentFolder();
            $docFolder->status = 1;
            $docFolder->idpeople_owner = $this->id;
            $docFolder->fs_folder_name = $this->id;
            $docFolder->folder_name_eng = $this->id . " " . WebLang::t('people', 'eng') . WebLang::sp('eng') . WebLang::t('folder', 'eng');
            $docFolder->folder_name_cht = $this->id . " " . WebLang::t('people', 'cht') . WebLang::sp('cht') . WebLang::t('folder', 'cht');
            $docFolder->folder_name_chs = $this->id . " " . WebLang::t('people', 'chs') . WebLang::sp('chs') . WebLang::t('folder', 'chs');
            $docFolder->folder_name_jap = $this->id . " " . WebLang::t('people', 'jap') . WebLang::sp('jap') . WebLang::t('folder', 'jap');

            if ($docFolder->validate() && $docFolder->save()) {
                $this->idfolder = $docFolder->id;

                if ($this->save()) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public function getGenderR() {
        return $this->hasOne(FormElement::className(), ['element_key' => 'gender'])
                        ->from(FormElement::tableName() . ' formElementGender')
                        ->onCondition(['formElementGender.element_type' => 'gender']);
    }

    /**
     * 
     * @param array $excludeIdpeoples
     * @return models
     */
    public static function getAllConsultantsWithNames($excludeIdpeoples) {
        $query = People::find()
                ->joinWith(['peopleRole'])
                ->where(['people.status' => 1, 'people_role.status' => 1])
                ->andWhere(['between', 'people_role.idpeople_role_type', 1, 10])
                ->andWhere(['not in', 'people.id', $excludeIdpeoples, 'idpeople']);

        if (Yii::$app->language == "eng") {
            $query->orderBy("co_name ASC, firstname_eng ASC, lastname_eng ASC");
        } else {
            $query->orderBy("co_name_locale ASC, lastname_locale ASC, firstname_locale ASC");
        }

        return $query->all();
    }

}
