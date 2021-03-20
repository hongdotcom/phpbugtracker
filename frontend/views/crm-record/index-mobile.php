<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\helpers\AifinHelper;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\jui\DatePicker;
use common\models\TaskCategory;
//use common\models\CrmRecordType;
use common\models\ClientSite;
use common\models\Progress;
use common\models\Priority;
use common\models\User;
use richardfan\widget\JSRegister;
use common\models\CrmRecord;
use yii\helpers\ArrayHelper;
use common\helpers\MobileDetect;

$detect = new MobileDetect;

// Any mobile device (phones or tablets).
if (!$detect->isMobile()) {
    die("Not mobile client");
}

if ($selfRecordsOnly == 1) {
    $this->params['breadcrumbs'][] = 'Your Tickets';
} else {
    $this->params['breadcrumbs'][] = 'All Tickets';
}
$user = Yii::$app->user->identity->id;
$currPro = Yii::$app->request->get('pro');
$idclients = AifinHelper::getIdclients($user);
frontend\assets\DropZoneAsset::register($this);
?>
<style>
    .grid-view td {
        white-space:normal !important; 
    }
    div .summary {
        padding: 15px;
    }
    thead {
        display: none;
    }
</style>    
<div class="crm-record-index table-responsive">
    <div class="col-xs-12">
        <div class="col-sm-12 hidden-xs">
            <div class="pull-right" style="margin-bottom: 20px">
                <?php foreach ($progresses as $progress) { ?>
                    <?php $count = $progress->getCrmRecordCount($selectedIdclient ? $selectedIdclient : $idclients, Yii::$app->user->identity->id) ?>
                    <?php if ($count > 0) { ?>
                        <a href="<?= Url::to(['crm-record/index', 'pro' => $progress->id]) ?>" class="btn btn-<?= Progress::getProgressCss($progress) ?>"
                           ><?= $progress->progress_name ?> (<?= $count ?>)</a>
                       <?php } ?>
                   <?php } ?>
            </div>
        </div>

        <div class="col-xs-12" style="padding: 0px !important;">
            <div class="pull-right">
                <?= Html::a("<i class='zmdi zmdi-plus'></i>", ['create'], ['class' => 'btn btn-warning']) ?>
                <a href="<?= Url::to(['crm-record/index', 'self' => 1]) ?>" class="btn btn-primary"><?= "Yours" ?></a>
                <a href="<?= Url::to(['crm-record/index', 'self' => 0]) ?>" class="btn btn-success"><?= "All" ?></a>
            </div>
            <input type="text" id="searchString" name="searchString" class="form-control input-xs" placeholder="search" style="width: auto !important;">  
        </div>
    </div>

    <?php if ($dataProviderOpenRecordsToYou) { ?>
        <!-- open -->
        <?=
        GridView::widget([
            'dataProvider' => $dataProviderOpenRecordsToYou,
            'tableOptions' => [
                'class' => 'table table-striped',
            ],
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
            'columns' => [
                ['label' => '',
                    'attribute' => 'idcrm_record_cat',
                    'format' => 'raw',
                    'value' => function($model, $selfRecordsOnly) {
//                        $created_at = Yii::$app->formatter->asDate($model->created_at, 'yyyy-MM-dd HH:MM');
                        $created_at = AifinHelper::showX1Ago($model->created_at);
                        $TaskCategory = TaskCategory::find()->
                                        where(['id' => $model->idcrm_record_cat])->one();

                        if ($model) {
                            $out = strlen($model->subject) > 90 ? substr($model->subject, 0, 90) . "..." : $model->subject;
                        } else {
                            $out = null;
                        }

                        $priority = Priority::find()
                                ->where(['id' => $model->idpriority])
                                ->one();

                        if ($priority) {
                            if ($model->idpriority <> 4) {
                                switch ($model->idpriority) {
                                    case 1:
                                    case 2:
                                        $cssClass = 'danger';
                                        break;
                                    case 3:
                                        $cssClass = 'warning';
                                        break;
                                    case 4:
                                        $cssClass = 'primary';
                                        break;
                                    case 5:
                                        $cssClass = 'success';
                                        break;
                                    case 6:
                                        $cssClass = 'default';
                                        break;
                                }
                                $priorityText = "<label class='label label-$cssClass'  style='margin-right: 10px;'>{$priority->priority_name}</label>";
                            } else {
                                $priorityText = '';
                            }
                        }

                        if ($progress = Progress::find()
                                ->where(['id' => $model->idprogress])
                                ->one()) {

                            switch ($progress->id) {
                                case Progress::OPEN:
                                    $css = "warning";
                                    break;
                                case Progress::CLIENT_REVIEW:
                                    $css = "info";
                                    break;
                                case Progress::CLOSE:
                                    $css = "success";
                                    break;
                                case Progress::INTERNAL_REVIEW:
                                    $css = "primary";
                                    break;
                                default:
                                    $css = "default";
                            }
                            $progressText = "<label class='label label-$css'>" . $progress->progress_name . "</label>";
                        } else {
                            $progressText = "";
                        }

                        $nameTag = "<span class='text-info'>From {$model->createdBy->name()}</span>";

                        if ($model->idassignee == Yii::$app->user->identity->id) {
                            $nameTag .= "<span class='text-danger'> to You</span>";
                        } else {
                            $nameTag .= "<span class='text-warning'> to " . CrmRecord::getAssignee($model->idassignee)->username;
                        }
                        $nameTag .= "</span>";

                        $out .= "<br>" .
                                "<label class='label label-default'>{$TaskCategory->task_name_eng}</label> " . " " . $progressText . " " . $priorityText .
                                "<br>" .
                                "<small style='color:#aaa' class='pull-right'>$created_at</small>";


                        return "<div class='col-xs-12' style='min-height: 50px'>" . "<a href='#' class='text-primary' onclick='toggleActivityModal(\"#activity-modal\", {$model->id})' .
                        >$out</a>" .
                        "<span class='pull-left'>$nameTag</span>" .
                        "</div>";
                    }
                ],
                [
//                    'headerOptions' => ['class' => 'col-xs-1'],
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}',
                    'visible' => (User::getIsAdmin($user))
                ],
                [
//                    'headerOptions' => ['class' => 'col-xs-1'],
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}',
                    'visible' => (User::getIsAdmin($user))
                ],
            ],
        ]);
        ?>
    <?php } ?>

    <?php if ($dataProviderClosedRecords) { ?>
        <!-- closed -->
        <?=
        GridView::widget([
            'dataProvider' => $dataProviderClosedRecords,
            'tableOptions' => [
                'class' => 'table table-striped table-condensed',
            ],
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '--'],
            'columns' => [
                ['label' => 'Client',
                    'attribute' => 'idclient',
                    'value' => function($model) {
                        $client = ClientSite::find()
                                ->where(['id' => $model->idclient])
                                ->one();
                        if ($client) {
                            return $client->client_name;
                        } else {
                            return null;
                        }
                    }
                ],
                ['label' => 'Category',
                    'attribute' => 'idcrm_record_cat',
                    'value' => function($model) {
                        $TaskCategory = TaskCategory::find()->
                                        where(['id' => $model->idcrm_record_cat])->one();
                        return $TaskCategory->task_name_eng;
                    }
                ],
//                ['label' => 'Priority',
//                    'attribute' => 'idpriority',
//                    'format' => 'raw',
//                    'value' => function($model) {
//                        $priority = Priority::find()
//                                ->where(['id' => $model->idpriority])
//                                ->one();
//                        if ($priority) {
//                            switch ($model->idpriority) {
//                                case 1:
//                                case 2:
//                                    $cssClass = 'danger';
//                                    break;
//                                case 3:
//                                    $cssClass = 'warning';
//                                    break;
//                                case 4:
//                                    $cssClass = 'primary';
//                                    break;
//                                case 5:
//                                    $cssClass = 'success';
//                                    break;
//                                case 6:
//                                    $cssClass = 'default';
//                                    break;
//                            }
//                            return "<label class='label label-$cssClass'>{$priority->priority_name}</label>";
//                        } else {
//                            return null;
//                        }
//                    }
//                ],
//                'updated_at',
                [
                    'label' => 'Worklog',
                    'format' => 'raw',
                    'value' => function($model) {
                        return "<a href='#' class='button btn btn-default' onclick='toggleActivityModal(\"#activity-modal\", {$model->id})' .
                        ><i class=\"zmdi zmdi-file\"></i></a>";
                    }
                ],
            ],
        ]);
        ?>
    <?php } ?>
</div>

<?=
$this->render('_crm-modal', [
    'model' => $model,
    'activityModel' => $activityModel,
    'user' => $user,
])
?>

<?php
JSRegister::begin([
    'position' => \yii\web\View::POS_END
]);
?>
<script>
    Dropzone.options.myDropzone = {
        uploadMultiple: true,
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

    function goSearch() {
        var s = $('input[id=searchString]').val()
        var url = "/crm-record/index?s=" + s;
        window.location.href = url;
    }

    function goUrl(url) {
        window.location.href = url;
    }

    $(document).ready(function () {
        $("#searchString").keypress(function (event) {
            if (event.which == 13) {
                event.preventDefault();
                goSearch();
            }
        });
    });

</script>

<?php JSRegister::end(); ?>