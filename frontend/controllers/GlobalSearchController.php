<?php

namespace backend\controllers;

use common\controllers\MasterController;
use common\models\ActionLog;
use common\models\LoginAttempt;
use common\models\Permission;
use common\models\WebLang;
use common\models\People;
use common\models\Plan;
use common\models\Document;
use common\models\CrmRecordRelation;
use common\models\PeopleRoleType;
use yii\helpers\Url;
use Yii;
use yii\filters\VerbFilter;

use yii\db\Expression;

class GlobalSearchController extends MasterController {

    public $limit = 20; // max no. of results per section
    public $plans = null;
    public $crm = null;
    public $people = null;
    public $dms = null;

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex() {
//        $globalSearch = $this->currentUser->checkPermission('client_global_search');

        $s = Yii::$app->request->post("s");
        $s = stripslashes(strtolower($s));

//        $searchStringArray = explode(" ", $s);
        $searchStringArray[] = trim($s);

        foreach ($searchStringArray as $item) {
            $item = strtolower($item);
            $item = trim($item);

            if (!empty($item)) {
                $this->searchBug($item);
           //     $this->searchPlan($item);
           //     $this->searchPeople($item);
           //     $this->searchDMS($item);
            }
        }

        return $this->render('index', [
                    'crm' => $this->crm,
                    'plans' => $this->plans,
                    'people' => $this->people,
                    'dms' => $this->dms,
                    'searchText' => $s,
        ]);
    }

    function searchPlan($searchPhrase) {
        $lang = Yii::$app->language;

        if (empty(trim($searchPhrase)))
            return FALSE;

        $results = Plan::find()
                ->joinWith([
                    "holders",
                    "writings",
                    "writings.people",
                    "servicings",
                    "servicings.people",
                    "product",
                    "product.productCategory",
                    "provider",
                    "statusDetail"])
                ->andFilterWhere([
                    'OR',
                    ["LIKE", "product.product_name_$lang", $searchPhrase],
                    ["LIKE", "provider.provider_name_$lang", $searchPhrase],
                    ["LIKE", "status_detail.status_name_$lang", $searchPhrase],
                    ["LIKE", "policy_no", $searchPhrase],
                    ["LIKE", "concat_ws(' ', writingPeople.co_name, writingPeople.firstname_eng, writingPeople.lastname_eng)", $searchPhrase],
                    ["LIKE", "concat_ws(' ', writingPeople.co_name, writingPeople.lastname_eng, writingPeople.firstname_eng)", $searchPhrase],
                    ["LIKE", "concat_ws(' ', writingPeople.co_name_locale, writingPeople.firstname_locale, writingPeople.lastname_locale)", $searchPhrase],
                    ["LIKE", "concat_ws(' ', writingPeople.co_name_locale, writingPeople.lastname_locale, writingPeople.firstname_locale)", $searchPhrase],
                    ["LIKE", "concat_ws(' ', servicingPeople.co_name, servicingPeople.firstname_eng, servicingPeople.lastname_eng)", $searchPhrase],
                    ["LIKE", "concat_ws(' ', servicingPeople.co_name, servicingPeople.lastname_eng, servicingPeople.firstname_eng)", $searchPhrase],
                    ["LIKE", "concat_ws(' ', servicingPeople.co_name_locale, servicingPeople.firstname_locale, servicingPeople.lastname_locale)", $searchPhrase],
                    ["LIKE", "concat_ws(' ', servicingPeople.co_name_locale, servicingPeople.lastname_locale, servicingPeople.firstname_locale)", $searchPhrase],
                    ["LIKE", "category_name_eng", $searchPhrase],
                    ["LIKE", "idplan_old", $searchPhrase],
                    ["LIKE", "application_no", $searchPhrase],
                    ["LIKE", "plan.id", $searchPhrase]
                ])
                ->limit($this->limit)
                ->all();

        foreach ($results as $result) {
            $category = WebLang::t("plan") . " " . $result->id;
            $resultDesc = "";
            $resultUrl = "";

            if ($result->policy_no) {
                $resultDesc .= WebLang::t("policy_no") . " " . trim($result->policy_no) . ", ";
            }

            if (isset($result->provider)) {
                $resultDesc .= $result->provider->name() . ", ";
            }

            if (isset($result->product)) {
                $resultDesc .= $result->product->name() . ", ";
            }

            if ($result->holders) {
                $resultDesc .= WebLang::t("holder") . " " . trim($result->holders[0]->people->name());
            }
            $resultDesc = rtrim($resultDesc, ',');
            $resultUrl = Url::to(["/plan/view"]) . "?id=" . $result->id;

            $this->plans[] = [
                "category" => $category,
                "resultDesc" => $resultDesc,
                "resultUrl" => $resultUrl
            ];
        }
    }

    function searchPeople($searchPhrase) {
        $lang = Yii::$app->language;

        $cols = [
            new Expression("distinct people.id"),
            new Expression("people_role_type.id as role_type"),
            new Expression("people_role_type.role_type_name_$lang as role_type_name"),
            new Expression("concat_ws(' ', co_name, firstname_eng, lastname_eng) as fullname"),
            new Expression("concat_ws('', co_name_locale, firstname_locale, lastname_locale) as fullname_locale"),
            "dob",
            "email_primary",
            "idpeople_old",
            "co_name",
            "co_name_locale",
            new Expression("country.country_name_$lang as nationality"),
            new Expression("people_role_type.role_type_name_$lang as pp_type_name")
        ];

        $results = People::find()
                ->joinWith([
                    "peopleRole",
                    "peopleRole.peopleRoleType",
                    "nationalityCountry",
                    "infoSets.idens",
                    "userPeoples",
                    "userPeoples.user"
                ])

                /*
                 * Data Control
                 */
//        $db->where("consultant.idpeople", $peopleTree, "IN");
                ->andFilterWhere([
                    'OR',
                    ["LIKE", "concat_ws(' ', people.co_name, people.firstname_eng, people.lastname_eng)", $searchPhrase],
                    ["LIKE", "concat_ws(' ', people.co_name, people.lastname_eng, people.firstname_eng)", $searchPhrase],
                    ["LIKE", "concat_ws('', people.co_name_locale, people.lastname_locale, people.firstname_locale)", $searchPhrase],
                    ["LIKE", "concat_ws('', people.co_name_locale, people.firstname_locale, people.lastname_locale)", $searchPhrase],
                    ["LIKE", "people.idpeople_old", $searchPhrase],
                    ["LIKE", "people.ref_no", $searchPhrase],
                    ["LIKE", "people.id", $searchPhrase],
                    ["LIKE", "people.dob", $searchPhrase],
                    ["LIKE", "lower(people.email_primary)", $searchPhrase],
                    ["LIKE", "lower(people_identification.id_no)", $searchPhrase],
                    ["LIKE", "lower(user.email)", $searchPhrase],
                ])
                ->andWhere("people.status <> " . Plan::DELETED)
                ->limit($this->limit)
                ->all();

        foreach ($results as $result) {
            $category = WebLang::t("people");
            $resultDesc = "";
            $resultUrl = "";
            
            if (isset($result->peopleRole->peopleRoleType)) {
                if ($result->peopleRole->idpeople_role_type == PeopleRoleType::CLIENT) {
                    $resultUrl = Url::to(["/client/view"]) . "?idpeople=" . $result->id;
                } elseif ($result->peopleRole->idpeople_role_type >= PeopleRoleType::AGENT1 && $result->peopleRole->idpeople_role_type <= PeopleRoleType::LEVEL10) {
                    $resultUrl = Url::to(["/consultant/view"]) . "?idpeople=" . $result->id;
                } elseif ($result->peopleRole->idpeople_role_type >= PeopleRoleType::STAFF) {
                    $resultUrl = Url::to(["/user/view"]) . "?idpeople=" . $result->id;
                }
                $resultDesc .= "<div class='col-sm-2'>" . $result->peopleRole->peopleRoleType->name() . "</div>";
                $resultDesc .= "<div class='col-sm-4'>" . $result->name("eng") . " " . $result->name("cht") . " (" . $result->id . ")</div>";
                $resultDesc .= " <div class='col-sm-3'><small>(" . WebLang::t('email_primary') . "</small>) {$result->email_primary}</div>";
                if (isset($result->userPeoples[0]->user->email)) {
//                    var_dump($result->userPeoples[0]); die();
                    $resultDesc .= " <div class='col-sm-3'><small>(" . WebLang::t('login_id') . "</small>) {$result->userPeoples[0]->user->email}</div>";
                }
            } else {
                continue;
                // User account has no peopleRole, ignore it in result
                // $resultUrl = Url::to(["/user/view"]) . "?idpeople=" . $result->id;
            }

            $this->people[] = [
                "category" => $category,
                "resultDesc" => $resultDesc,
                "resultUrl" => $resultUrl
            ];
        }
    }

    function searchBug($searchPhrase) {
        $lang = Yii::$app->language;

        $searchPhrase = stripcslashes($searchPhrase);

        $results = CrmRecordRelation::find()
                ->joinWith([
                    "crmRecord",
                    "crmRecord.crmRecordType",
                    "crmRecord.crmActivities",
                    "client_site",
                    "priority",
                    "progress",
                    "user",
                    
                ])
                ->andFilterWhere([
                    'OR',
                    ["LIKE", "crmRecord.id", $searchPhrase],
                    ["LIKE", "crmRecord.remark", $searchPhrase],
                    ["LIKE", "client_site.client_name", $searchPhrase],
                    ["LIKE", "priority.priority_name", $searchPhrase],
                    ["LIKE", "progress.progress_name", $searchPhrase],
                    ["LIKE", "user.username", $searchPhrase],
                ])
                ->limit($this->limit)
                ->all();

        /*
         * Data Control
         */

        foreach ((array) $results as $result) {
            $category = WebLang::t("crm_record") . " " . $result->id;
            $resultDesc = "";
            $resultUrl = "";

            if ($result->crmRecord->crmRecordType) {
                $resultDesc .= $result->crmRecord->crmRecordType->name() . ", ";
            }

            if ($result->crmRecord->reminder_at) {
                $resultDesc .= WebLang::t('reminder_at') . " " . $result->crmRecord->reminder_at . ", ";
            }

            if ($result->people) {
                $resultDesc .= "" . $result->people->name() . ", ";
            }

            if (!empty($result->idplan)) {
                $resultDesc .= WebLang::t("plan") . " " . $result->idplan . ", ";
            }

            $resultDesc = rtrim($resultDesc, ', ');

            if ($result->idplan > 0) {
                $resultUrl = Url::to(["/plan/view"]) . "?id={$result->idplan}#tab-crm";
            } elseif ($result->idpeople > 0) {
                $resultUrl = Url::to(["/client/view"]) . "?id={$result->idpeople}";
            } else {
                continue;
            }
            $this->crm[] = [
                "category" => $category,
                "resultDesc" => $resultDesc,
                "resultUrl" => $resultUrl
            ];
        }
    }

    function searchDMS($searchPhrase) {
        $lang = Yii::$app->language;

        $results = Document::find()
                ->joinWith([
                    "documentFolder",
                ])

                /*
                 * Data Control
                 */
//        $db->where("consultant.idpeople", $peopleTree, "IN");
                ->andFilterWhere([
                    'OR',
                    ["LIKE", "doc_desc_eng", $searchPhrase],
                    ["LIKE", "doc_desc_cht", $searchPhrase],
                    ["LIKE", "doc_desc_chs", $searchPhrase],
                    ["LIKE", "doc_desc_jap", $searchPhrase],
                    ["LIKE", "document_folder.folder_name_eng", $searchPhrase],
                    ["LIKE", "document_folder.folder_name_cht", $searchPhrase],
                    ["LIKE", "document_folder.folder_name_chs", $searchPhrase],
                    ["LIKE", "document_folder.folder_name_jap", $searchPhrase],
                ])
                ->andWhere("document.status = 1")
                ->andWhere("document_folder.status = 1")
                ->limit($this->limit)
                ->all();

        foreach ($results as $result) {
            $category = $result->documentFolder->name();
            $resultDesc = "";
            $resultUrl = "";

            if ($name = $result->documentFolder->name()) {
                $resultDesc .= "" . $name . "</label> ";
            }
            if ($name = $result->name()) {
                $resultDesc .= $name;
            }

            $resultUrl = Url::to(["/dms/index"]) . "?f=" . $result->idfolder;

            $this->dms[] = [
                "category" => $category,
                "resultDesc" => $resultDesc,
                "resultUrl" => $resultUrl
            ];
        }
    }

}
