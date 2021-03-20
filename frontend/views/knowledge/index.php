<?php

use common\models\WebLang;
use common\helpers\AifinHelper;
use yii\helpers\Url;
use richardfan\widget\JSRegister;
use yii\widgets\ActiveForm;
use common\models\BbsCategory;
use yii\helpers\Html;

$lang = Yii::$app->language;

$this->params['breadcrumbs'][] = ['label' => WebLang::t('knowledge_base')];

$categoryModel = new BbsCategory();
$categoryModel->type = "kb";
?>
<style>
    .list-group-item {
        border-bottom: 1px #ccc solid !important;
    }
</style>

<div class="col-sm-12 text-center">
    <h2 class="text-primary lead"><?= WebLang::t('knowledge_base') ?> <small><?= WebLang::t('please_select_a_category_or_search') ?></small></h2>
    <div class="col-md-6 col-md-offset-3 col-sm-12">
        <div class="gswrapper">
            <?php $form = ActiveForm::begin(['id' => 'kb-search', 'action' => Url::to(['knowledge/search'])]); ?>
            <input id="autocomplete" class="gsfi w-100" autocomplete="off" name="s" placeholder="<?= WebLang::t('search') ?>">
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<div class="col-lg-10 col-lg-offset-1 col-md-12 col-sm-12 reading">

    <div class="col-sm-12 text-center m-t-10">
        <?php foreach ($models as $model) { ?>
            <a href="<?= Url::to(['knowledge/category']) ?>?id=<?= $model->id ?>" class="btn btn-lg btn-outline-color m-b-10"><?= $model->$lang ?></a>
        <?php } ?>
    </div>

    <div class="col-md-6 col-sm-12">
        <div class="list-group">
            <div class="lg-header">
                <?= WebLang::t('popular_articles') ?>
            </div>
            <div class="lg-body">
                <?php foreach ($popularArticles as $popularArticle) { ?>
                    <a class="list-group-item media" href="<?= Url::to(['knowledge/article']) . "?id=" . $popularArticle->id ?>">
                        <div class="media-body">
                            <div class="lgi-heading">
                                <?= $popularArticle->subject ?>
                            </div>
                            <div class="pull-right"><?= AifinHelper::icon('text-info', AifinHelper::SIGN_GOTO) ?></div>
                            <small class="lgi-text"><label class="label label-info"><?= $popularArticle->bbsBoard->$lang ?></label> 
                                <?= $popularArticle->updated_at ? AifinHelper::datetime($popularArticle->updated_at) : AifinHelper::datetime($popularArticle->created_at) ?>
                            </small>
                        </div>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12">
        <div class="list-group">
            <div class="lg-header">
                <?= WebLang::t('recent_articles') ?>
            </div>
            <?php foreach ($recentArticles as $recentArticle) { ?>
                <a class="list-group-item media" href="<?= Url::to(['knowledge/article']) . "?id=" . $recentArticle->id ?>">
                    <div class="media-body">
                        <div class="lgi-heading"><?= $recentArticle->subject ?></div>
                        <div class="pull-right"><?= AifinHelper::icon('text-info', AifinHelper::SIGN_GOTO) ?></div>
                        <small class="lgi-text"><label class="label label-info"><?= $recentArticle->bbsBoard->$lang ?></label> 
                            <?= AifinHelper::datetime($recentArticle->created_at) ?></small>
                    </div>
                </a>
            <?php } ?>
        </div>
    </div>

    <div class="col-sm-12 text-center m-t-30">
        <h2><?= WebLang::t('kb_index_message') ?></h2>
        <a href="mailto:<?= common\models\Settings::$key['cs_email'] ?>" class="btn btn-outline-color"><?= WebLang::t('please_contact_us_immediately') ?></a>
    </div>
</div>

<a id="new-category" class="btn btn-danger btn-float m-btn"><i class="zmdi zmdi-plus"></i></a>

<div class="modal fade in" id="categoryModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button"><?= AifinHelper::SIGN_CLOSE ?></button>
                <h3 class="modal-title text-warning"><?= WebLang::t('category') ?></h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'category-form', 'enableClientValidation' => true]) ?>
            <input type="hidden" name="token" id='token' value="<?= AifinHelper::genSingingToken(['id' => $categoryModel->id]) ?>">
            <?= Html::hiddenInput('id', !$categoryModel->isNewRecord ? $categoryModel->id : null) ?>
            <?= $form->field($categoryModel, 'type')->hiddenInput(['value' => 'kb'])->label(false); ?>

            <div class="modal-body">
                <div class="col-sm-4">
                    <?= $form->field($categoryModel, 'seq')->textInput(['class' => 'form-control'])->label(WebLang::t('seq')) ?>
                </div>
                <div class="col-sm-12">
                    <?= $form->field($categoryModel, 'eng')->textInput(['class' => 'form-control'])->label(WebLang::t('eng')) ?>
                </div>
                <div class="col-sm-12">
                    <?= $form->field($categoryModel, 'cht')->textInput(['class' => 'form-control'])->label(WebLang::t('cht')) ?>
                </div>
                <div class="col-sm-12">
                    <?= $form->field($categoryModel, 'chs')->textInput(['class' => 'form-control'])->label(WebLang::t('chs')) ?>
                </div>
                <div class="col-sm-12">
                    <?= $form->field($categoryModel, 'jap')->textInput(['class' => 'form-control'])->label(WebLang::t('jap')) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success pull-right form-submit" type="button"
                        ><span class="form-spinner hidden"><?= AifinHelper::SIGN_SPINNER ?></span> <?= WebLang::t('save') ?></button>
                <button data-dismiss="modal" class="btn btn-warning pull-right m-r-10" type="button"> <?= WebLang::t('cancel') ?></button>
            </div>
            <?php $form->end(); ?>
        </div>
    </div>
</div>

<?php
JSRegister::begin([
    'position' => \yii\web\View::POS_END
]);
?>
<script>
    $('#category-form .form-submit').click(function (e) {
        e.preventDefault();
        formId = 'category-form';
        id = $('#category-form input[name=id]').val();
        if (!checkForm(formId)) {
            return false;
        }
        data = $('#' + formId).serializeArray();

        $.ajax({
            url: "<?= Url::to(['bbs/category']) ?>?id=" + id,
            dataType: 'json',
            data: data,
            cache: false,
            type: 'POST',
            success: function (response) {
                if (response.success) {
                    $("#categoryModal").modal('toggle');
                    notify(t.saved_success, 'success');
                    $.pjax.reload('#categories', {timeout: false, url: pjaxBoardUrl});
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

    $('#new-category').click(function (e) {
        $("#categoryModal .modal-title").html('<?= WebLang::t("create") . WebLang::sp() . WebLang::t("category") ?>');
        $("#categoryModal").modal({backdrop: 'static', keyboard: false});
    });

    $('#autocomplete').focus();
</script>
<?php JSRegister::end(); ?>