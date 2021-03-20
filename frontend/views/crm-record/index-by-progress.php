<?php

use common\helpers\AifinHelper;
use richardfan\widget\JSRegister;
use yii\helpers\Url;
use common\models\WebLang;

if ($showClosedOnly == 1) {
    $this->params['breadcrumbs'][] = ['label' => 'Closed Tickets', 'url' => ['index']];
} else {
    $this->params['breadcrumbs'][] = ['label' => 'Open Tickets', 'url' => ['index']];
}

$user = Yii::$app->user->identity->id;
$currPro = Yii::$app->request->get('pro');
$idclients = AifinHelper::getIdclients($user);
//frontend\assets\DropZoneAsset::register($this);
?>
<a href="create" class="btn m-btn btn-float"><i class='zmdi zmdi-plus zmdi-hc-2x'></i></a>

<div class="col-md-4 col-xs-4">
    <div class="">
        <?php $form = \yii\bootstrap\ActiveForm::begin() ?>
        <input type="text" id="searchString" name="s" class="form-control" placeholder="search" value="<?= $searchPhase ?>">
        <?php $form->end() ?>
    </div>
</div>

<div class="col-md-8 col-xs-8">
    <?php if ($showClosedOnly) { ?>
        <a href="/crm-record/index?f=0&c=<?= $idclient ?>" class="btn btn-info m-b-5 m-l-10 pull-right">Show open tickets</a>
    <?php } else { ?>
        <a href="/crm-record/index?f=1&c=<?= $idclient ?>" class="btn btn-info m-b-5 m-l-10 pull-right">Show closed tickets</a>
    <?php } ?>
    <div class="pull-right">
        <select id="idclient" class="form-control">
            <option value=""><?= WebLang::t('filter_by_client') ?></option>
            <?php foreach ((array) $clients as $client) { ?>
                <option value="<?= $client['id'] ?>" <?= $idclient > 0 && ($client['id'] == $idclient) ? "selected" : "" ?>><?= $client['client_name'] ?> (<?= $client['cnt'] ?> <?= WebLang::t('items') ?>)</option>
            <?php } ?>
        </select>
    </div>
</div>

<div class="clearfix"></div>

<div style="margin-top: 15px;">
    <?=
    $this->render("_crm-records-by-progress", [
        'progresses' => $progresses,
        'user' => $user,
        'showClosedOnly' => $showClosedOnly,
        'searchPhase' => $searchPhase,
        'idclient' => $idclient,
        'excludeCategories' => $excludeCategories,
    ])
    ?>
</div>

<div class="modal fade modal-dialog-center " id="activity-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" style='width: 95% !important;'>
        <div class="modal-content-wrap">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">X</button>
                    <h2 class="modal-title lead text-uppercase text-warning">Manage Ticket</h2>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
</div>

<?php
JSRegister::begin([
    'position' => \yii\web\View::POS_END
]);
?>
<script>
//    Dropzone.options.myDropzone = {
//        uploadMultiple: true,
//        maxFilesize: <?= AifinHelper::file_upload_max_size("mb") ?>, // mb
//        autoDiscover: false,
//        clickable: true,
//        addRemoveLinks: true,
//        init: function () {
//            this.on("addedfile", function (file, done) {
//                if (file.size > this.options.maxFilesize * 1024 * 1024) {
//                    swal({
//                        title: "File too large",
//                        text: 'Max size is ' + this.options.maxFilesize + " Mb",
//                        type: "error",
//                    });
//                    this.removeAllFiles();
//                }
//            });
//            this.on("success", function (file) {
//                //$.pjax.reload('#contents', {timeout: false, url: ajaxUrl});
//                window.location.reload();
//            });
//            this.on("queuecomplete", function (file) {
//                this.removeAllFiles(true);
//            });
//        }
//    }

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

        $(document).on("change", "#idclient", function () {
            window.location.href = "<?= Url::to(['crm-record/index']) ?>?f=<?= $showClosedOnly ?>&c=" + $(this).val();
        });
    });

</script>

<?php JSRegister::end(); ?>
