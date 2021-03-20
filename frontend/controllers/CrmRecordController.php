<?php

namespace frontend\controllers;

use common\models\Progress;
use Yii;
use common\models\CrmRecord;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\helpers\AifinHelper;
use common\controllers\MasterController;
use common\models\TaskCategory;
use common\models\CrmActivity;
use common\models\UserClientRelationship;
use common\models\User;
use common\models\DmsModel;
use common\helpers\ApiHandler;
//use common\models\ClientSite;
//use common\helpers\MobileDetect;
use common\models\WebLang;

class CrmRecordController extends MasterController {

    /**
     * ? = guest
     * @ = authenticated usewr
     * @return type
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
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
     * Lists all Open CrmRecord models.
     * @return mixed
     */
    public function actionIndex() {
        $searchPhase = Yii::$app->request->post('s');
        $showClosedOnly = intval(Yii::$app->request->get('f', 0));
        $idclient = intval(Yii::$app->request->get('c', null));
        $mode = intval(Yii::$app->request->get('m', 1));

        switch ($mode) {
            case 1:
                $excludeCategories = [TaskCategory::MEMO, TaskCategory::WORKLOG];
                break;
            case 2:
                $excludeCategories = [1, 2, 3, 4, 5, 6, 7, 8, 10, 12, 13];
                break;
        }

        $model = new CrmRecord;

        if ($showClosedOnly == 1) {
            $title = WebLang::t('Closed Tickets');
        } else {
            $title = WebLang::t('Open Tickets');
        }

        $categories = TaskCategory::find()->where(['status' => 1])->orderBy('task_name_eng ASC')->all();

        if ($showClosedOnly) {
            $progresses = Progress::find()->where(['status' => 1])
                    ->andWhere(['=', 'id', Progress::CLOSE])
                    ->orderBy('seq ASC')
                    ->all();
        } else {
            $progresses = Progress::find()->where(['status' => 1])
                    ->andWhere(['<>', 'id', Progress::CLOSE])
                    ->orderBy('seq ASC')
                    ->all();
        }

        $dmsModels = new DmsModel;

        $this->layout = "fullwidth";
        $view = 'index-by-progress';

        $clients = \common\models\CrmRecord::find()
                ->select(['client_site.id', 'idclient', 'client_name', new \yii\db\Expression("count(1) as cnt")])
                ->joinWith(['clientSite'])
                ->where(['is not', 'idclient', null])
                ->andWhere(['crm_record.status' => 1, 'client_site.status' => 1])
                ->andWhere(['is', 'crm_record.effective_date', null])
                ->andWhere(['<>', 'crm_record.idprogress', Progress::CLOSE])
                ->andWhere(['not in', 'crm_record.idcrm_record_cat', [9, 11]])
                ->groupBy("client_site.id")
                ->asArray()
                ->all();

        return $this->render($view, [
                    'model' => $model,
                    'clients' => $clients,
                    'idclient' => $idclient,
                    'sort' => ['defaultOrder' => ['idpriority' => SORT_DESC]],
                    'categories' => $categories,
                    'progresses' => $progresses,
                    'dmsModels' => $dmsModels,
                    'showClosedOnly' => $showClosedOnly,
                    'title' => $title,
                    'searchPhase' => $searchPhase,
                    'excludeCategories' => $excludeCategories,
        ]);
    }

    private function showMainPage($showClosedOnly) {
        
    }

    /**
     * Displays a single CrmRecord model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $model = $this->findModel($id);
        $users = \yii\helpers\ArrayHelper::map(User::find()
                                ->select(['user.id', 'user.username'])
                                ->joinWith('userRoleRelationship')
                                ->where(['IN', 'idrole', [1, 2]])
                                ->all(),
                        'id', 'username');

        if (Yii::$app->request->isAjax) {
            return $this->renderPartial('view', [
                        'model' => $model,
                        'idcrm_record' => $id,
                        'users' => $users,
            ]);
        }
        return $this->render('view', [
                    'model' => $model,
                    'users' => $users,
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
        $model->status = 1;

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

        $this->layout = "fullwidth";
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

}
