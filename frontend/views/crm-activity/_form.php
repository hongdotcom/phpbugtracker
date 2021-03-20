<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Settings;

/* @var $this yii\web\View */
/* @var $model common\models\CrmActivity */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="crm-activity-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'idcrm_record')->textInput() ?>

    <?= $form->field($model, 'action_date')->textInput() ?>

    <?= $form->field($model, 'internal_remark')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'agent_remark')->textarea(['rows' => 6]) ?>

    <?php if (Settings::$key['client_service'] == 1) { ?>
        <?= $form->field($model, 'client_remark')->textarea(['rows' => 6]) ?>
    <?php } ?>

    <?= $form->field($model, 'reminder_date')->textInput() ?>

    <?= $form->field($model, 'email_notification')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <?= $form->field($model, 'deleted_by')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'progress')->textInput() ?>

    <?= $form->field($model, 'request_date')->textInput() ?>

    <?= $form->field($model, 'request_to')->textInput() ?>

    <?= $form->field($model, 'received_date')->textInput() ?>

    <?= $form->field($model, 'received_from')->textInput() ?>

    <?= $form->field($model, 'delivery_date')->textInput() ?>

    <?= $form->field($model, 'delivery_to')->textInput() ?>

    <?= $form->field($model, 'provider_remark')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
