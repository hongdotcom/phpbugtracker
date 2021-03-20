<?php

use yii\helpers\Html;
use common\helpers\AifinHelper;
use yii\helpers\Url;
use common\models\Progress;
use richardfan\widget\JSRegister;

if ($selfRecordsOnly == 1) {
    $this->params['breadcrumbs'][] = 'Your Tickets';
} else {
    $this->params['breadcrumbs'][] = 'All Tickets';
}
$user = Yii::$app->user->identity->id;
$currPro = Yii::$app->request->get('pro');
$idclients = AifinHelper::getIdclients($user);
//frontend\assets\DropZoneAsset::register($this);
?>
<div class="crm-record-index table-responsive">
    <div class="col-sm-12 m-t-10">
        <div class="pull-right">
            <a href="<?= Url::to(['crm-record/index', 'closed' => $showClosedOnly == 1 ? 0 : 1]) ?>" class="btn btn-default"
                       >Toggle Status</a>
            
            <?php foreach ($progresses as $progress) { ?>
                <?php $count = $progress->getCrmRecordCount($selectedIdclient ? $selectedIdclient : $idclients, Yii::$app->user->identity->id) ?>
                <?php if ($count > 0) { ?>
                    <a href="<?= Url::to(['crm-record/index', 'pro' => $progress->id]) ?>" class="btn btn-<?= Progress::getProgressCss($progress) ?>"
                       ><?= $progress->progress_name ?> (<?= $count ?>)</a>
                   <?php } ?>
               <?php } ?>
        </div>

        <div class="col-lg-6 col-sm-12">
            <div class="col-sm-6">
                <input type="text" id="searchString" name="searchString" 
                       class="form-control input-xs " placeholder="search">  
            </div>
            <?= Html::a("<i class='zmdi zmdi-plus'></i>", ['create'], ['class' => 'btn btn-warning']) ?>
            <a href="<?= Url::to(['crm-record/index', 'self' => 1]) ?>" class="btn btn-primary"><?= "My records" ?></a>
            <a href="<?= Url::to(['crm-record/index', 'self' => 0]) ?>" class="btn btn-success"><?= "All records" ?></a>
        </div>

        <?php if ($dataProviderOpenRecordsToYou) { ?>
            <?=
            $this->render("_crm-records", [
                'dataProviderOpenRecords' => $dataProviderOpenRecordsToYou,
                'title' => common\models\WebLang::t('Open Tickets assigned to You'),
                'user' => $user,
            ])
            ?>
        <?php } ?>

        <?php if ($dataProviderOpenRecordsFromYou) { ?>
            <?=
            $this->render("_crm-records", [
                'dataProviderOpenRecords' => $dataProviderOpenRecordsFromYou,
                'title' => common\models\WebLang::t('Open Tickets created by You'),
                'user' => $user,
            ])
            ?>
        <?php } ?>

        <?php if ($dataProviderClosedRecords) { ?>
            <?=
            $this->render("_crm-records", [
                'dataProviderOpenRecords' => $dataProviderClosedRecords,
                'title' => common\models\WebLang::t('Closed Tickets'),
                'user' => $user,
            ])
            ?>
        <?php } ?>
    </div>
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
