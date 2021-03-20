<?php

namespace frontend\controllers;

use Yii;
use common\models\CrmActivity;
use common\controllers\MasterController;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\helpers\ApiHandler;
use common\helpers\AifinHelper;
use common\models\Progress;
use common\models\DmsModel;
use common\models\CrmRecord;
use common\models\User;
use common\models\UserClientRelationship;

/**
 * CrmRecordController implements the CRUD actions for CrmRecord model.
 */
class CrmActivityController extends MasterController {

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
        $model = new CrmActivity;
        $dataProvider = new ActiveDataProvider([
            'query' => CrmActivity::find()->with('crm_record')
        ]);

        return $this->render('index', [
                    'model' => $model,
                    'dataProvider' => $dataProvider,
                        //'sort'=> ['defaultOrder' => ['created_at'=>SORT_DESC]]
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

        return $this->render('view', [
                    'model' => $model,
        ]);
    }

    public function actionViewRelated($idcrm_record) {
        $model = $this->findModel($idcrm_record);

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
        $model = new CrmActivity();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            return $this->redirect(['view', 'id' => $model->id]);
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
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes an existing CrmRecord model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CrmRecord model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CrmRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = CrmActivity::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionSave() {
        if (Yii::$app->request->isAjax) {

            $CrmActivity = Yii::$app->request->post("CrmActivity");
            $user = Yii::$app->user->identity->id;
            $formCrmRecord = Yii::$app->request->post("CrmRecord"); // optional

            if (isset($CrmActivity['id'])) {
                $model = $this->findModel($CrmActivity['id']);
            } else {
                $model = new CrmActivity;
            }

            if ($model->load(Yii::$app->request->post())) {
                if (!empty(trim($model->internal_remark))) {
                    if ($model->isNewRecord) {
                        $model->created_at = date(AifinHelper::DATETIMEFORMAT);
                        $model->created_by = $user;
                    } else {
                        $model->updated_at = date(AifinHelper::DATETIMEFORMAT);
                    }

                    if (!$model->save()) {
                        ApiHandler::failure(AifinHelper::modelErrorHtml($model->getErrors()));
                    }
                }

                if(!$crmRecord = $model->crmRecord) {
                    ApiHandler::failure("Ticket not found");
                }

                // update NEXT assignee if provided
                if (isset($formCrmRecord['idassignee'])) {
                    $crmRecord->idassignee = $formCrmRecord['idassignee'];
                }
                if (isset($formCrmRecord['idprogress'])) {
                    $crmRecord->idprogress = $formCrmRecord['idprogress'];
                }
                $crmRecord->updated_by = Yii::$app->user->identity->id;
                $crmRecord->updated_at = date(AifinHelper::DATETIMEFORMAT);

                if ($crmRecord->load(Yii::$app->request->post()) && $crmRecord->save()) {
//                    \Yii::$app->getSession()->setFlash('success', 'Ticket closed with work log');
//                } else {
//                    \Yii::$app->getSession()->setFlash('success', 'Ticket closed with work log');
                }

                /*
                 * handle activity attachment
                 */
                $dmsModel = new DmsModel();
                if (!User::getIsAdmin($user)) {

                    $adminUsers = UserClientRelationship::getAllReceipients('1');
                    $adminEmail = User::getEmails($adminUsers);
                    $clientEmail = [];
                } else {
                    $clientUsers = UserClientRelationship::getAllReceipients($model->crmRecord->idclient);
                    $clientEmail = User::getEmails($clientUsers);
                    $adminUsers = UserClientRelationship::getAllReceipients('1');
                    $adminEmail = User::getEmails($adminUsers);
                }

                if (!$dmsModel->upload([
                            'idcrm_activity' => $model->id,
                                ]
                        )) {
                    ApiHandler::failure();
                } else {

                    \Yii::$app
                            ->mailer
                            ->compose(
                                    ['text' => 'newActivitySubmit-text'], ['user' => Yii::$app->user->identity, 'detail' => $model, 'subject' => $crmRecord]
                            )
                            ->setFrom('cs@calc.asia')
                            ->setTo(array_merge($adminEmail, $clientEmail))
                            ->setSubject('New aifin Support Action Updated ')
                            ->send();
//                    \Yii::$app->getSession()->setFlash('success', 'Work log saved');
                    ApiHandler::success();
                }
            }
        }
        ApiHandler::failure();
    }

}
