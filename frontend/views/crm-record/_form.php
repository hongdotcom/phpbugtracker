<?php

use yii\widgets\ActiveForm;
use common\models\CrmRecordType;
use common\models\ClientSite;
use common\models\User;
use common\models\Progress;
use common\models\Priority;
use common\models\TaskCategory;
use yii\helpers\ArrayHelper;
use common\helpers\AifinHelper;
use yii\jui\DatePicker;
use richardfan\widget\JSRegister;

frontend\assets\DropZoneAsset::register($this);
?>

<div class="crm-record-form col-sm-12">
    <?php
    $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
    $user = Yii::$app->user->identity->id;
    $defaultClient = AifinHelper::getIdclient($user);
    ?>

    <div class="col-md-6 col-sm-12">
        <?= $form->field($model, 'subject')->textInput()->label('Subject'); ?> 
    </div>

    <div class="clearfix"></div>

    <div class="col-sm-12">
        <?= $form->field($model, 'remark')->textarea(['rows' => 6])->label('Content') ?>
    </div>
    <div class="col-md-3 col-sm-12">
        <?=
        $form->field($model, 'requested_at')->widget(DatePicker::classname(), [
            'options' => ['autocomplete' => 'off', 'readOnly' => false, 'class' => 'form-control'],
            'clientOptions' => [
            ],
            'dateFormat' => 'yyyy-MM-dd',
        ])->label('Request Date');
        ?>
    </div>
    <div class="col-md-3 col-sm-12">
        <?=
        $form->field($model, 'reminder_at')->widget(DatePicker::classname(), [
            'options' => ['autocomplete' => 'off', 'readOnly' => false, 'class' => 'form-control'],
            'clientOptions' => [
            ],
            'dateFormat' => 'yyyy-MM-dd',
        ])->label('Reminder Date');
        ?>
    </div>
    <div class="col-md-3 col-sm-12">
        <?=
        $form->field($model, 'effective_date')->widget(DatePicker::classname(), [
            'options' => ['autocomplete' => 'off', 'readOnly' => false, 'class' => 'form-control'],
            'clientOptions' => [
            ],
            'dateFormat' => 'yyyy-MM-dd',
        ])->label('Completion Date');
        ?>
    </div>

    <div class="col-md-4 col-sm-12">
        <?=
        $form->field($model, 'idcrm_record_cat')->dropDownList(
                ArrayHelper::map(TaskCategory::find()->where(['status' => 1])->orderBy('task_name_eng ASC')->all(), 'id', 'task_name_eng'), [
            'prompt' => '- please select -',
        ]);
        ?>
    </div>
    <div class="col-md-4 col-sm-12">
        <?=
        $form->field($model, 'idcrm_record_type')->dropDownList(
                ArrayHelper::map(CrmRecordType::find()->orderBy('crm_record_type_name_eng ASC')->all(), 'id', 'crm_record_type_name_eng'), [
            'prompt' => '- please select -',
        ]);
        ?>
    </div>

    <div class="clearfix"></div>
    <?php if (User::getIsAdmin($user)) { ?>
        <div class="col-md-4 col-sm-12">
            <?=
            $form->field($model, 'idpriority')->dropDownList(
                    ArrayHelper::map(Priority::find()->all(), 'id', 'priority_name'), [
                'prompt' => '- please select -',
                    ]
            );
            ?>
        </div>
    <?php } ?>

    <?php //if (User::getIsAdmin($user) || $model->created_by == Yii::$app->user->identity->id || $model->idassignee == Yii::$app->user->identity->id) { ?>
    <div class="col-md-3 col-sm-12">
        <?=
        $form->field($model, 'idassignee')->dropDownList(
                ArrayHelper::map(User::find()
                                ->select(['user.id', 'user.username'])
                                ->joinWith('userRoleRelationship')
                                ->where(['IN', 'idrole', [1, 2]])
                                ->all(), 'id', 'username'), [
            'prompt' => 'Select Assignee',
        ]);
        ?>
    </div>
    <?php //} ?>

    <?php if (User::getIsAdmin($user)) { ?>
        <div class="col-md-3 col-sm-12">
            <?=
            $form->field($model, 'idclient')->dropDownList(
                    ArrayHelper::map(ClientSite::find()->all(), 'id', 'client_name'), [
                'prompt' => 'Select Client',
            ]);
            ?> 
        </div>
    <?php } ?>

    <?php if (User::getIsAdmin($user)) { ?>
        <div class="col-md-3 col-sm-12">
            <?=
            $form->field($model, 'idprogress')->dropDownList(
                    ArrayHelper::map(Progress::find()
                                    ->orderBy([
                                        'seq' => SORT_ASC,
                                    ])
                                    ->all(), 'id', 'progress_name'), [
                'prompt' => 'Select Progress',
                    ]
            );
            ?>
        </div>
    <?php } ?>

    <?php if (User::getIsAdmin($user) || $model->created_by == Yii::$app->user->identity->id) { ?>
        <div class="col-md-3 col-sm-12">
            <?= $form->field($model, "client_report")->checkbox(['uncheck' => 0, 'label' => "<span class='text-info strong f-20'>Client Report</span>"]) ?>
        </div>     
        <div class="col-md-3 col-sm-12">
            <?= $form->field($model, "internal_report")->checkbox(['uncheck' => 0, 'label' => "<span class='text-info strong f-20'>Internal Report</span>"]) ?>
        </div>
    <?php } ?>

    <div class="clearfix"></div>
    <?php
    $documents = $model->documents;
    if (empty($documents)) {
        ?>    
        <input type="file" id="files" name="file">
        <?php
    } else {
        foreach ((array) $documents as $document) {
            echo '<a class="btn btn-info" href="/dms/download?a=2&id=' . $document->id . '&token=' . $document->token . '"   target="_new"> ' .
            $document->orig_file_name . '</a>';
        }
    }
    ?>
    <?php if ($model->created_by) { ?>
        <div class=pull-right>Ticker created by <?= $model->createdBy->name() ?></div>
    <?php } ?>
    <div class="form-group" style="margin-top: 25px;">
        <button  class="btn btn-success"  onclick="this.submit(); this.disabled = true;" type="Submit"> Submit</button>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
JSRegister::begin([
    'position' => \yii\web\View::POS_END
]);
?>
<script>
    Dropzone.options.myDropzone = {
        uploadMultiple: false,
        maxFilesize: <?= AifinHelper::file_upload_max_size("mb") ?>, // mb
        autoDiscover: false,
        clickable: true,
        addRemoveLinks: true,
        init: function () {
            this.on("addedfile", function (file, done) {
                if (file.size > this.options.maxFilesize * 1024 * 1024) {
                    swal({
                        title: "File too large",
                        text: 'Max size is ' + this.options.maxFilesize + " Mb",
                        type: "error",
                    });
                    this.removeAllFiles();
                }
            });
            this.on("success", function (file) {
                //$.pjax.reload('#contents', {timeout: false, url: ajaxUrl});
                window.location.reload();
            });
            this.on("queuecomplete", function (file) {
                this.removeAllFiles(true);
            });
        }
    }



</script>

<?php JSRegister::end(); ?>