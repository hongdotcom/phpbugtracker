<?php

namespace frontend\controllers;

use common\models\Progress;
use Yii;
use common\models\CrmRecord;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use common\helpers\AifinHelper;
use common\controllers\MasterController;
use common\models\TaskCategory;
use common\models\CrmActivity;
use common\models\User;
use common\models\DmsModel;
use common\helpers\ApiHandler;
use common\helpers\MobileDetect;
use yii\helpers\ArrayHelper;
use common\models\WebLang;
use common\models\FreeBusy;
use common\models\TimeSlot;

class CalendarController extends MasterController {

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
     * Lists all CrmRecord models.
     * @return mixed
     */
    public function actionIndex() {
        $date = Yii::$app->request->get('date', date(AifinHelper::DATEFORMAT));
        $action = Yii::$app->request->get('action');
        $idtimeslot = Yii::$app->request->get('idtimeslot');
        $fullDayBusy = Yii::$app->request->get('fdb');
        $fullDayFree = Yii::$app->request->get('fdf');

        $today = AifinHelper::fixDateFormat($date);
        $msg = NULL;

        if (($action == 0 || $action == 1) && $idtimeslot > 0 && ($idtimeslot <> TimeSlot::FULLDAY)) {
            $updateFreebusy = FreeBusy::find()
                    ->joinWith(['timeSlot'])
                    ->where([
                        'freebusy.status' => 1,
                        'event_date' => $date,
                        'idtimeslot' => $idtimeslot,
                        'created_by' => Yii::$app->user->identity->id,
                    ])
                    ->orderBy("seq ASC")
                    ->one();

            if (!$updateFreebusy) {
                $updateFreebusy = new FreeBusy();
                $updateFreebusy->created_by = Yii::$app->user->identity->id;
                $updateFreebusy->status = 1;
                $updateFreebusy->idtimeslot = $idtimeslot;
                $updateFreebusy->event_date = $date;
                $updateFreebusy->created_at = date(AifinHelper::DATETIMEFORMAT);
            }
            if ($action == FreeBusy::FREE) {
                $updateFreebusy->delete();
            } else {
                $updateFreebusy->freebusy = $action;
                $updateFreebusy->updated_at = date(AifinHelper::DATETIMEFORMAT);
                $updateFreebusy->updated_by = Yii::$app->user->identity->id;
                if (!$updateFreebusy->save()) {
                    $msg = "Update schedule error - " . AifinHelper::modelErrorHtml($updateFreebusy->getErrors());
                }
            }
        } else {
            if ($idtimeslot == TimeSlot::FULLDAY) {
                $freebusys = FreeBusy::find()
                        ->joinWith(['timeSlot'])
                        ->where([
                            'freebusy.status' => 1,
                            'event_date' => $date,
                            'created_by' => Yii::$app->user->identity->id,
                        ])
                        ->all();

                // always delete all existing freebusys if fdf or fdb provided
                foreach ((array) $freebusys as $freebusy) {
                    $freebusy->delete();
                }

                if ($fullDayBusy == 1 || $action == 0) {
                    $timeslot = TimeSlot::find()->where(['full_day' => 1, 'status' => 1])->one();

                    $updateFreebusy = new FreeBusy();
                    $updateFreebusy->created_by = Yii::$app->user->identity->id;
                    $updateFreebusy->freebusy = 0;
                    $updateFreebusy->status = 1;
                    $updateFreebusy->idtimeslot = $timeslot->id;
                    $updateFreebusy->event_date = $date;
                    $updateFreebusy->created_at = date(AifinHelper::DATETIMEFORMAT);

                    if (!$updateFreebusy->save()) {
                        $msg = "Update schedule error - " . AifinHelper::modelErrorHtml($updateFreebusy->getErrors());
                    }
                }
            }
        }
        $lang = AifinHelper::getLang();
//        $detect = new MobileDetect;
// Any mobile device (phones or tablets).
//        if ($detect->isMobile()) {
//            $view = 'index-mobile';
//        } else {
        $view = 'index';
//        }

        $timeslots = TimeSlot::find()->orderBy("seq ASC")->all();

        $freebusys = FreeBusy::find()
                ->joinWith(['timeSlot'])
                ->where([
                    'freebusy.status' => 1,
                    'event_date' => $today,
                ])
                ->orderBy("seq ASC")
                ->all();

        $this->layout = "fullwidth";
        return $this->render($view, [
                    'timeslots' => $timeslots,
                    'freebusys' => $freebusys,
                    'today' => $today,
                    'msg' => $msg,
//                    "freebusys" => $freebusys,
        ]);
    }

    /**
     * Displays a single CrmRecord model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax) {
            $data = $this->renderPartial('_detail-view', [
                'model' => $model,
                'idcrm_record' => $id,
            ]);
            return ApiHandler::custom([
                        'success' => true,
                        'data' => $data,
                        'idassignee' => $model->idassignee,
                            ]
            );
        }
        return $this->render('view', [
                    'model' => $model,
        ]);
    }

    /**
     * Creates a new CrmRecord model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $user = Yii::$app->user->getId('user');
        $model = new CrmRecord();
        $model->client_report = 0;
        $model->internal_report = 0;
        $model->created_at = date(AifinHelper::DATEFORMAT);
        $model->requested_at = date(AifinHelper::DATEFORMAT);
        $model->reminder_at = AifinHelper::adjustDate('7D');
        $model->idprogress = 1;
        $model->created_by = Yii::$app->user->identity->id;
        $model->updated_by = Yii::$app->user->identity->id;
        $model->idpriority = 4;
        $model->idcrm_record_cat = 1;

        $model->idclient = 1;
        $model->idcrm_record_type = 23;

        if (!User::getIsAdmin($user)) {
            $model->idclient = User::getIdclient(Yii::$app->user->identity->id)->idclient;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->created_at = date(AifinHelper::DATETIMEFORMAT);

            if (!$model->save()) {
                $errorMsg = AifinHelper::modelErrorHtml($model->getErrors());
                \Yii::$app->getSession()->setFlash('error', $errorMsg);
            } else {
                $dmsModel = new DmsModel();
                if (!User::getIsAdmin($user)) {

                    $adminUsers = UserClientRelationship::getAllReceipients('1');
                    $adminEmail = User::getEmails($adminUsers);
                    $clientEmail = [];
                } else {
                    $clientUsers = UserClientRelationship::getAllReceipients($model->idclient);
                    $clientEmail = User::getEmails($clientUsers);
                    $adminUsers = UserClientRelationship::getAllReceipients('1');
                    $adminEmail = User::getEmails($adminUsers);
                }

                if (!$dmsModel->upload([
                            'idcrm_record' => $model->id,
                                ]
                        )) {
                    $errorMsg = "Upload Document failed";
                    \Yii::$app->getSession()->setFlash('error', $errorMsg);
                } else {
                    \Yii::$app->getSession()->setFlash('success', 'Ticket saved');
                }
                \Yii::$app
                        ->mailer
                        ->compose(
                                ['text' => 'newTicketSubmit-text'], ['user' => Yii::$app->user->identity, 'detail' => $model], ['content' => $model->remark, 'subject' => $model->subject]
                        )
                        ->setFrom('cs@calc.asia')
                        ->setTo(array_merge($adminEmail, $clientEmail))
                        ->setSubject('New aifin Support Ticket Submitted')
                        ->send();
                return $this->redirect(['/crm-record/index']);
            }
        }

        return $this->render('create', [
                    'model' => $model,
        ]);
    }

    /**
     * Updates an existing CrmRecord model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {

        $user = Yii::$app->user->getId('user');
        if ($user) {
            $idclient = AifinHelper::getIdclient($user);
        }
        if (CrmRecord::checkPermission($id, $idclient) || User::getIsAdmin($user)) {


            $model = $this->findModel($id);
            $model->updated_by = Yii::$app->user->identity->id;
            $model->updated_at = date(AifinHelper::DATETIMEFORMAT);

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['index']);
            }

            return $this->render('update', [
                        'model' => $model,
            ]);
        } else {
            return $this->redirect(['index']);
        }
    }

    /**
     * Deletes an existing CrmRecord model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {

        $user = Yii::$app->user->getId('user');
        if ($user) {
            $idclient = AifinHelper::getIdclient($user);
        }
        if (CrmRecord::checkPermission($id, $idclient) || User::getIsAdmin($user)) {
            $this->findModel($id)->delete();

            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the CrmRecord model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CrmRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = CrmRecord::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionDatafeed($month) {
        $lang = AifinHelper::getLang();

//        $this->isAjax();

        $return['items'] = [];
        $month = intval($month);
        /*
         * By default show 3 months records only
         */
        switch ($month) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
                $months = [$month, $month + 1, $month + 2];
                break;
            case 11:
                $months = [11, 12, 1];
                break;
            case 12:
                $months = [12, 1, 2];
                break;
        }

        /*
         * Public Holidays
         */
        $holidays = \common\models\PublicHolidays::find()
                ->where([
                    'status' => 1,
                ])
                ->andWhere(['>=', 'event_date', date(AifinHelper::DATEFORMAT)])
                ->orderBy("event_date")
                ->all();

        if ($holidays) {
            foreach ($holidays as $holiday) {
                $color = 'default';
                $eventName = "<span class='f-14 strong text-danger'><i class='zmdi zmdi-calendar-close'></i> " . $holiday["event_name_$lang"] . "</span>";

                $return['items'][] = [
                    'description' => WebLang::t('holiday'),
                    'name' => $eventName,
                    'image' => "",
                    'day' => date("d", strtotime($holiday['event_date'])),
                    'month' => date("m", strtotime($holiday['event_date'])),
                    'year' => date("Y", strtotime($holiday['event_date'])),
                    'color' => $color,
                    'time' => "",
                    'idtimeslot' => "",
                    'duration' => "1",
                    'location' => "",
                ];
            }
        }

        /*
         * FreeBusy
         */
        $results = FreeBusy::find()
                ->where(['status' => 1])
                ->andWhere([
                    'in', 'month(event_date)', $months
                ])
                ->andWhere(['>=', 'event_date', date(AifinHelper::DATEFORMAT)])
                ->orderBy(['event_date' => SORT_ASC, 'idtimeslot' => SORT_ASC])
                ->all();

        foreach ((array) $results as $freebusy) {
            if ($freebusy->freebusy == FreeBusy::BUSY) {
                $eventName = "<i class=\"zmdi zmdi-close-circle\"></i> ";
                $color = 5;
            } else {
                $eventName = "<i class=\"zmdi zmdi-check-circle\"></i> ";
                $color = 3;
            }
            $eventName .= $freebusy->timeSlot->name() . " " .
                    $freebusy->createdBy->name() .
//                    (YII_DEBUG ? " #{$freebusy->id}" : "") . 
                    "";


            $return['items'][] = [
                'name' => $eventName,
                'image' => "",
                'day' => date("d", strtotime($freebusy->event_date)),
                'month' => date("m", strtotime($freebusy->event_date)),
                'year' => date("Y"),
                'time' => $freebusy->timeSlot->name(),
                'idtimeslot' => $freebusy->idtimeslot,
                'color' => $color,
                'duration' => "1",
                'location' => null,
                'id' => $freebusy->id,
            ];
        }
        ApiHandler::successRaw($return);
        Yii::$app->end();
    }

}
