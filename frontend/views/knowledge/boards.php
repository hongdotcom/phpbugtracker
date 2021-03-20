<?php

use common\models\WebLang;
use common\helpers\AifinHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use richardfan\widget\JSRegister;
use yii\helpers\Html;

$lang = Yii::$app->language;
$this->params['breadcrumbs'][] = ['label' => WebLang::t('knowledge_base'), 'url' => Url::to(['knowledge/index'])];
$this->params['breadcrumbs'][] = ['label' => $selectedBoardModel->bbsCategory->$lang, 'url' => Url::to(['knowledge/category']) . "?id=" . $selectedBoardModel->idcategory];
$this->params['breadcrumbs'][] = $selectedBoardModel->$lang;

\frontend\assets\SummernoteAsset::register($this);

$cleansedTemplate = "";
?>

<style>
    #content {
        line-height: 1.8;
    }

</style>

<div class="col-lg-10 col-lg-offset-1 col-md-12 col-sm-12 reading">

    <div class="col-md-4 col-sm-12">
        <div class="gswrapper">
            <?php $form = ActiveForm::begin(['id' => 'kb-search', 'action' => Url::to(['knowledge/search'])]); ?>
            <input id="autocomplete" class="gsfi w-100" autocomplete="off" name="s" placeholder="<?= WebLang::t('search') ?>">
            <?php ActiveForm::end(); ?>
        </div>
        <?php if ($categoryModels) { ?>
            <div class="list-group">
                <div class="lg-header">
                    <h2 class="text-info"><?= WebLang::t('bbs_board') ?></h2>
                </div>
                <div class="lg-body">
                    <?php foreach ($boardModels as $model) { ?>
                        <a class="list-group-item media<?= $selectedBoardModel->id == $model->id ? " active" : "" ?>" 
                           href="<?= Url::to(['knowledge/board']) . "?id=" . $model->id ?>">
                            <div class="media-body">
                                <div class="pull-right"><?= AifinHelper::icon('text-info', AifinHelper::SIGN_GOTO) ?></div>
                                <div class="lgi-heading">
                                    <?= $model->$lang ?>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="col-md-8 col-sm-12">
        <div class="list-group">
            <div class="lg-header">
                <h2 class="text-info">
                    <?= WebLang::t('article') ?>
                    <?php if (AifinHelper::checkPermission('kb_delete')) { ?>
                        <a class="text-danger pointer pull-right" id="delete-board" 
                           onclick="deleteBoard(<?= $selectedBoardModel->id ?>, '<?= AifinHelper::genSingingToken(['id' => (int) $selectedBoardModel->id]) ?>')"
                           ><?= AifinHelper::SIGN_TRASH_1X ?>
                        </a>
                    <?php } ?>
                    <?php if (AifinHelper::checkPermission('kb_update')) { ?>
                        <a id="edit-board" class="text-primary pointer m-r-10 pull-right"><?= AifinHelper::SIGN_MODIFY ?></a>
                    <?php } ?>
                </h2>
            </div>
            <div class="lg-body">
                <?php if ($postModels) { ?>
                    <?php foreach ((array) $postModels as $post) { ?>
                        <a class="list-group-item media" href="<?= Url::to(['knowledge/article']) . "?id=" . $post->id ?>">
                            <div class="media-body">
                                <div class="pull-right"><?= AifinHelper::icon('text-info', AifinHelper::SIGN_GOTO) ?></div>
                                <div class="lgi-heading">
                                    <h4><?= $post->subject ?> <?= $post->sticky == 1 ? "<label class='label label-info f-11'>" . WebLang::t('sticky') . "</label>" : "" ?>
                                        <br><small><?= $model->created_at ?></small>
                                    </h4>
                                </div>
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

<!-- Create post -->
<?php if (AifinHelper::checkPermission('kb_create')) { ?>
    <a id="new-post" class="btn btn-danger btn-float m-btn"><i class="zmdi zmdi-plus"></i></a>

    <div class="modal fade in" id="postModal" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">x</button>
                    <h3 class="modal-title text-warning"><?= WebLang::t('article') ?></h3>
                </div>
                <?php $form = ActiveForm::begin(['id' => 'form1', 'enableClientValidation' => true]) ?>
                <input type="hidden" name="uuid" id='uuid' value="">
                <input type="hidden" name="idboard" id='idboard' value="<?= $selectedBoardModel->id ?>">

                <div class="modal-body">
                    <div class="row">
                        <div id="subject-wrapper" class="">
                            <div class="col-sm-12">
                                <div class='form-group'>
                                    <div class='fg-line'>
                                        <label for='subject'><?= WebLang::t('subject') ?></label>
                                        <input type='text' name='subject' id='subject' class='form-control'>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="html-editor-1"></div>
                        </div>
                        <div class="pull-right">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-warning"
                            type="button"> <?= WebLang::t('cancel') ?></button>
                    <button class="btn btn-sm btn-primary waves-effect" type="button" id="btn-post-save"><?= WebLang::t('submit') ?></button>
                </div>
                <?php $form->end(); ?>
            </div>
        </div>
    </div>
<?php } ?>

<?php if (AifinHelper::checkPermission('kb_update')) { ?>
    <div class="modal fade in" id="boardModal" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button"><?= AifinHelper::SIGN_CLOSE ?></button>
                    <h3 class="modal-title text-warning"><?= WebLang::t('bbs_board') ?></h3>
                </div>
                <?php $form = ActiveForm::begin(['id' => 'board-form', 'enableClientValidation' => true]) ?>
                <input type="hidden" name="token" id='token' value="<?= AifinHelper::genSingingToken(['id' => (int) $selectedBoardModel->id]) ?>">
                <?= Html::hiddenInput('id', !$selectedBoardModel->isNewRecord ? $selectedBoardModel->id : null) ?>
                <?= $form->field($selectedBoardModel, 'idcategory')->hiddenInput(['value' => $selectedBoardModel->idcategory])->label(false); ?>

                <div class="modal-body">
                    <div class="col-sm-4">
                        <?= $form->field($selectedBoardModel, 'seq')->textInput(['class' => 'form-control'])->label(WebLang::t('seq')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($selectedBoardModel, 'eng')->textInput(['class' => 'form-control'])->label(WebLang::t('eng')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($selectedBoardModel, 'cht')->textInput(['class' => 'form-control'])->label(WebLang::t('cht')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($selectedBoardModel, 'chs')->textInput(['class' => 'form-control'])->label(WebLang::t('chs')) ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($selectedBoardModel, 'jap')->textInput(['class' => 'form-control'])->label(WebLang::t('jap')) ?>
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

    $('#new-post').click(function (e) {
        $("#postModal .modal-title").html('<?= WebLang::t('create') . WebLang::sp() . WebLang::t('article') ?>');
        $("#subject-wrapper").removeClass('hidden');
        $("#post-tag-wrapper").removeClass('hidden');
        $("input[name='idparent']").val('');
        $("input[name='subject']").val('');
        $("input[name='BbsPostTag[id]']").val('');
        $("#postModal").modal({backdrop: 'static', keyboard: false});
    });

    if ($('.html-editor-1')[0]) {
        //Edit
        $('.html-editor-1').summernote({
            focus: true,
            height: '100%',
            minHeight: 300,
            callbacks: {
                onImageUpload: function (files, editor, $editable) {
                    sendFile('.html-editor-1', files[0], editor, $editable);
                },
                onPaste: function (e) {
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    // Firefox fix
                    setTimeout(function () {
                        document.execCommand('insertText', false, bufferText);
                    }, 10);
                }
            }
        });
        $('.hec-save').show();
//        $('.html-editor-1').code("<?= $cleansedTemplate ?>");
    }

    $('#btn-post-save').click(function (e) {
        e.preventDefault();
        var markupStr = $(".html-editor-1").summernote('code');

        data = $('#form1').serializeArray();
        data.push({name: "post_content", value: markupStr});

        $.ajax({
            url: "<?= Url::to(['knowledge/post']) ?>",
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
                    swal(t.failure, (response.msg ? response.msg : t.save_failed), "error");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                notify(t.save_failed, 'danger');
            }
        });
    });

    function deleteBoard(id, token) {
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
                    url: "<?= Url::to(['knowledge/board-delete']) ?>?id=" + id,
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
    $('#edit-board').click(function (e) {
        $("#boardModal .modal-title").html('<?= WebLang::t("update") . WebLang::sp() . WebLang::t("bbs_board") ?>');
        $("#boardModal").modal({backdrop: 'static', keyboard: false});
    });

    $('#board-form .form-submit').click(function (e) {
        e.preventDefault();
        formId = 'board-form';
        if (!checkForm(formId)) {
            return false;
        }
        data = $('#' + formId).serializeArray();

        $.ajax({
            url: "<?= Url::to(['knowledge/board']) ?>?id=<?= $selectedBoardModel->id ?>",
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
