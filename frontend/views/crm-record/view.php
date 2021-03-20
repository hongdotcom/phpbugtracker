<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\Progress;
use common\models\CrmActivity;
use common\helpers\AifinHelper;
use common\models\User;
use yii\helpers\ArrayHelper;

$crmActivityModel = new CrmActivity;
$crmActivityModel->idcrm_record = $model->id;
$crmActivityModel->status = 1;
$crmActivityModel->action_date = date(AifinHelper::DATEFORMAT);
$crmActivityModel->created_by = Yii::$app->user->identity->id;

$this->title = 'View';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-sm-12">
        <div class='col-lg-6 col-sm-12'>
            <?=
            $this->render('_detail-view', [
                'model' => $model,
                'idcrm_record' => $idcrm_record,
            ])
            ?>
        </div>

        <div class='col-lg-6 col-sm-12'>
            <h2 class="lead text-primary">Add a reply</h2>

            <?php $form = ActiveForm::begin(['id' => 'worklog-form']); ?>
            <input type="hidden" name="CrmActivity[idcrm_record]" value="<?= $crmActivityModel->idcrm_record ?>">

            <?php if ($model->idprogress != Progress::CLOSE) { ?>
                <div class="col-sm-12">
                    <?= $form->field($crmActivityModel, 'internal_remark')->textarea(['rows' => 6, 'placeHolder' => 'Please provide content'])->label(false) ?>
                </div>
                <div class="col-sm-12">
                    <input type="file" id="files" name="file">
                </div>
            <?php } ?>

            <div class='clearfix'></div>
            <div class='m-t-25'>
                <?php if ($model->idprogress != Progress::CLOSE) { ?>
                    <div class="m-t-10">
                        <div class="col-md-4 col-xs-12">
                            <?=
                            $form->field($model, 'idcrm_record_cat')->dropDownList(
                                    ArrayHelper::map(common\models\TaskCategory::find()
                                                    ->orderBy([
                                                        'task_name_eng' => SORT_ASC,
                                                    ])
                                                    ->all(), 'id', 'task_name_eng'), ['prompt' => '- select category -']
                            );
                            ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($model->idprogress != Progress::CLOSE) { ?>
                    <div class="m-t-10">
                        <div class="col-md-4 col-xs-12">
                            <?= $form->field($model, 'idassignee')->dropDownList($users, ['prompt' => '- select assignee -'])->label("New Assignee") ?>
                        </div>
                        <div class="col-md-4 col-xs-12">
                            <?=
                            $form->field($model, 'idprogress')->dropDownList(
                                    ArrayHelper::map(Progress::find()
                                                    ->orderBy([
                                                        'seq' => SORT_ASC,
                                                    ])
                                                    ->all(), 'id', 'progress_name'), ['prompt' => '- select progress -']
                            );
                            ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <?php if ($model->idprogress != Progress::CLOSE) { ?>
            <div class="col-sm-12 text-center">
                <button  class="btn btn-success" onclick="submitWorklog('#worklog-form'); this.disabled = true;" type="button"> Submit</button>
                <button data-dismiss="modal" class="btn btn-danger" type="button"> Cancel</button>
            </div>
        <?php } ?>

        <div class="clearfix"></div>
        <div class="ticket-header m-t-25"></div>
        <div class="ticket-activities"></div>
    </div>
</div>

<?php ActiveForm::end() ?>
