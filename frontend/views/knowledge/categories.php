<?php

use common\models\WebLang;
use common\helpers\AifinHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use richardfan\widget\JSRegister;
use yii\helpers\Html;

$lang = Yii::$app->language;
$this->params['breadcrumbs'][] = ['label' => WebLang::t('knowledge_base'), 'url' => Url::to(['knowledge/index'])];
$this->params['breadcrumbs'][] = $selectedCategoryModel->$lang;

$bbsBoard = new \common\models\BbsBoard();
$bbsBoard->idcategory = $selectedCategoryModel->id;
?>

<style>
    #content {
        line-height: 1.8;
    }

</style>
<div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-12 reading">

    <div class="col-md-4 col-sm-12">
        <div class="gswrapper">
            <?php $form = ActiveForm::begin(['id' => 'kb-search', 'action' => Url::to(['knowledge/search'])]); ?>
            <input id="autocomplete" class="gsfi w-100" autocomplete="off" name="s" placeholder="<?= WebLang::t('search') ?>">
            <?php ActiveForm::end(); ?>
        </div>
        <?php if ($categoryModels) { ?>
            <div class="list-group">
                <div class="lg-header">
                    <h2 class="text-info"><?= WebLang::t('categories') ?></h2>
                </div>
                <div class="lg-body">
                    <?php foreach ($categoryModels as $categoryModel) { ?>
                        <a class="list-group-item media<?= $selectedCategoryModel->id == $categoryModel->id ? " active" : "" ?>" 
                           href="<?= Url::to(['knowledge/category']) . "?id=" . $categoryModel->id ?>">
                            <div class="media-body">
                                <div class="pull-right"><span class="m-t-25"><?= AifinHelper::icon('text-info', AifinHelper::SIGN_GOTO) ?></span></div>
                                <div class="lgi-heading"><?= $categoryModel->$lang ?></div>
                                <small class="lgi-text"></small>
                            </div>
                            <?php if ($selectedCategoryModel->id == $categoryModel->id) { ?>
                                <span class="arrowIcon"></span>
                            <?php } ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="col-sm-8">
        <div class="list-group">
            <div class="lg-header">
                <h2 class="text-info"><?= WebLang::t('bbs_board') ?>
                    <?php if (AifinHelper::checkPermission('kb_delete') && $selectedCategoryModel->protected <> 1) { ?>
                        <a class="text-danger pointer pull-right" id="delete-category" 
                           onclick="deleteCategory(<?= $selectedCategoryModel->id ?>, '<?= AifinHelper::genSingingToken(['id' => (int) $selectedCategoryModel->id]) ?>')"
                           ><?= AifinHelper::SIGN_TRASH_1X ?>
                        </a>
                    <?php } ?>
                    <?php if (AifinHelper::checkPermission('kb_update')) { ?>
                        <a id="edit-category" class="text-primary pointer m-r-10 pull-right"><?= AifinHelper::SIGN_MODIFY ?></a>
                    <?php } ?>
                </h2>
            </div>
            <div class="lg-body">
                <?php if ($models) { ?>
                    <?php foreach ((array) $models as $model) { ?>
                        <div class="pull-right m-t-15">
                            <span class=""><?= AifinHelper::icon('text-info', AifinHelper::SIGN_GOTO) ?></span>
                        </div>
                        <a class="list-group-item media" href="<?= Url::to(['knowledge/board']) . "?id=" . $model->id ?>">
                            <div class="media-body">
                                <div class="lgi-heading"><?= $model->$lang ?></div>
                                <small class="lgi-text"><?= $model->description ?></small>
                            </div>
                        </a>
                    <?php } ?>
                <?php } else { ?>
                    <h2 class="lead text-center"><?= WebLang::t('no_matching_record') ?></h2>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php if (AifinHelper::checkPermission('kb_create')) { ?>
    <a id="new-board" class="btn btn-danger btn-float m-btn"><i class="zmdi zmdi-plus"></i></a>

    <div class="modal fade in" id="boardModal" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button"><?= AifinHelper::SIGN_CLOSE ?></button>
                    <h3 class="modal-title text-warning"><?= WebLang::t('bbs_board') ?></h3>
                </div>
                <?php $form = ActiveForm::begin(['id' => 'board-form', 'enableClientValidation' => true]) ?>
                <input type="hidden" name="token" id='token' value="<?= AifinHelper::genSingingToken(['id' => (int) $bbsBoard->id]) ?>">
                <?= Html::hiddenInput('id', !$bbsBoard->isNewRecord ? $bbsBoard->id : null) ?>
                <?= $form->field($bbsBoard, 'idcategory')->hiddenInput(['value' => $bbsBoard->idcategory])->label(false); ?>

                <div class="modal-body">
                    <div class="col-sm-4">
                        <?= $form->field($bbsBoard, 'seq')->textInput(['class' => 'form-control'])->label(WebLang::t('seq')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($bbsBoard, 'eng')->textInput(['class' => 'form-control'])->label(WebLang::t('eng')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($bbsBoard, 'cht')->textInput(['class' => 'form-control'])->label(WebLang::t('cht')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($bbsBoard, 'chs')->textInput(['class' => 'form-control'])->label(WebLang::t('chs')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($bbsBoard, 'jap')->textInput(['class' => 'form-control'])->label(WebLang::t('jap')) ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-warning"
                            type="button"> <?= WebLang::t('cancel') ?></button>
                    <button type="button" class="btn btn-success pull-right form-submit" type="button"
                            ><span class="form-spinner hidden"><?= AifinHelper::SIGN_SPINNER ?></span> <?= WebLang::t('save') ?></button>
                </div>
                <?php $form->end(); ?>
            </div>
        </div>
    </div>
<?php } ?>

<?php if (AifinHelper::checkPermission('kb_update')) { ?>
    <div class="modal fade in" id="categoryModal" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button"><?= AifinHelper::SIGN_CLOSE ?></button>
                    <h3 class="modal-title text-warning"><?= WebLang::t('category') ?></h3>
                </div>
                <?php $form = ActiveForm::begin(['id' => 'category-form', 'enableClientValidation' => true]) ?>
                <?= Html::hiddenInput('id', !$selectedCategoryModel->isNewRecord ? $selectedCategoryModel->id : null) ?>
                <input type="hidden" name="token" id='token' value="<?= AifinHelper::genSingingToken(['id' => (int) $selectedCategoryModel->id]) ?>">
                <?= $form->field($bbsBoard, 'type')->hiddenInput(['value' => $selectedCategoryModel->type])->label(false); ?>

                <div class="modal-body">
                    <div class="col-sm-4">
                        <?= $form->field($selectedCategoryModel, 'seq')->textInput(['class' => 'form-control'])->label(WebLang::t('seq')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($selectedCategoryModel, 'eng')->textInput(['class' => 'form-control'])->label(WebLang::t('eng')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($selectedCategoryModel, 'cht')->textInput(['class' => 'form-control'])->label(WebLang::t('cht')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($selectedCategoryModel, 'chs')->textInput(['class' => 'form-control'])->label(WebLang::t('chs')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($selectedCategoryModel, 'jap')->textInput(['class' => 'form-control'])->label(WebLang::t('jap')) ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-warning"
                            type="button"> <?= WebLang::t('cancel') ?></button>
                    <button type="button" class="btn btn-success pull-right form-submit" type="button"
                            ><span class="form-spinner hidden"><?= AifinHelper::SIGN_SPINNER ?></span> <?= WebLang::t('save') ?></button>
                </div>
                <?php $form->end(); ?>
            </div>
        </div>
    </div>
<?php } ?>

<?php
JSRegister::begin([
    'position' => \yii\web\View::POS_END
]);
?>
<script>
    $('#board-form .form-submit').click(function (e) {
        e.preventDefault();
        formId = 'board-form';
        if (!checkForm(formId)) {
            return false;
        }
        data = $('#' + formId).serializeArray();

        $.ajax({
            url: "<?= Url::to(['knowledge/board']) ?>?id=",
            dataType: 'json',
            data: data,
            cache: false,
            type: 'POST',
            success: function (response) {
                if (response.success) {
                    swal({title: t.success, type: "success"
                    },
                            function () {
                                location.reload();
                            });
                } else {
                    swal({
                        title: t.failure,
                        text: (response.msg ? response.msg : t.save_failed),
                        type: "error",
                        html: true,
                        allowOutsideClick: true
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                notify(t.save_failed, 'danger');
            }
        });
    });

<?php if ($selectedCategoryModel->protected <> 1) { ?>
        function deleteCategory(id, token) {
            swal({
                title: t.are_you_sure,
                text: t.unrecoverable_warning,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: t.confirm,
                cancelButtonText: t.no_dont,
                closeOnConfirm: true,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: "<?= Url::to(['knowledge/category-delete']) ?>?id=" + id,
                        dataType: 'json',
                        data: {
                            'token': token
                        },
                        cache: false,
                        type: 'POST',
                        success: function (response) {
                            if (response.success) {
                                swal({title: t.success, type: "success"
                                },
                                        function () {
                                            window.location.href = '<?= Url::to(['knowledge/index']) ?>';
                                        });
                            } else {
                                swal({
                                    title: t.failure,
                                    text: (response.msg ? response.msg : t.save_failed),
                                    type: "error",
                                    html: true,
                                    allowOutsideClick: true
                                });
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            notify(t.save_failed, 'danger');
                        }
                    });
                }
            });
        }
<?php } ?>

    $('#new-board').click(function (e) {
        $("#boardModal .modal-title").html('<?= WebLang::t("create") . WebLang::sp() . WebLang::t("board") ?>');
        $("#boardModal").modal({backdrop: 'static', keyboard: false});
    });
    $('#edit-category').click(function (e) {
        $("#categoryModal .modal-title").html('<?= WebLang::t("update") . WebLang::sp() . WebLang::t("category") ?>');
        $("#categoryModal").modal({backdrop: 'static', keyboard: false});
    });
    $('#category-form .form-submit').click(function (e) {
        e.preventDefault();
        formId = 'category-form';
        if (!checkForm(formId)) {
            return false;
        }
        data = $('#' + formId).serializeArray();

        $.ajax({
            url: "<?= Url::to(['knowledge/category']) ?>?id=<?= $selectedCategoryModel->id ?>",
                        dataType: 'json',
                        data: data,
                        cache: false,
                        type: 'POST',
                        success: function (response) {
                            if (response.success) {
                                swal({title: t.success, type: "success"
                                },
                                        function () {
                                            location.reload();
                                        });
                            } else {
                                swal({
                                    title: t.failure,
                                    text: (response.msg ? response.msg : t.save_failed),
                                    type: "error",
                                    html: true,
                                    allowOutsideClick: true
                                });
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            notify(t.save_failed, 'danger');
                        }
                    });
                });

</script>
<?php JSRegister::end(); ?>
