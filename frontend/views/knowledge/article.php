<?php

use common\models\WebLang;
use common\helpers\AifinHelper;
use yii\helpers\Url;
use richardfan\widget\JSRegister;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\widgets\Select2;
use yii\web\JsExpression;

$lang = Yii::$app->language;

$this->params['breadcrumbs'][] = ['label' => WebLang::t('knowledge_base'), 'url' => Url::to(['knowledge/index'])];
$this->params['breadcrumbs'][] = ['label' => $model->bbsBoard->bbsCategory->$lang, 'url' => Url::to(['knowledge/category']) . "?id=" . $model->bbsBoard->idcategory];
$this->params['breadcrumbs'][] = ['label' => $model->bbsBoard->$lang, 'url' => Url::to(['knowledge/board']) . "?id=" . $model->idboard];

frontend\assets\SummernoteAsset::register($this);

//dd($postTagModels);
?>

<style>
    #content {
        line-height: 1.8;
    }
    #content img {
        max-width: 100% !important;
    }
</style>

<script>var ajaxUrl = "<?= Url::to(['knowledge/article']) . "?id=" . $model->id ?>";</script>

<div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-12 reading">
    <div class="pull-right">
        <div class="gswrapper">
            <?php $form = ActiveForm::begin(['id' => 'kb-search', 'action' => Url::to(['knowledge/search'])]); ?>
            <input id="autocomplete" class="gsfi w-100" autocomplete="off" name="s" placeholder="<?= WebLang::t('search') ?>">
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="col-sm-12">
        <a class="pull-right toggle-sticky btn <?= $model->sticky == 1 ? 'btn-info' : 'btn-default' ?> m-t-10"><?= WebLang::t('sticky') ?></a>
        <h2 class="text-default m-t-0"><?= $model->subject ?>
            <?php if ($model->slug) { ?>
                <br>
                <label class="label label-default f-12 m-r-5"><?= WebLang::t('slug') ?></label><small><?= e($model->slug) ?></small>
            <?php } ?>
        </h2>
        <?php if ($model->createdBy) { ?>
            <h4>
                <?= $model->createdBy->name() ?> <br><small><?= $model->created_at ?></small>
            </h4>
        <?php } ?>
    </div>

    <a class="text-danger pointer delete-post pull-right" onclick="deletePost(<?= $model->id ?>, '<?= AifinHelper::genSingingToken(['id' => (int) $model->id]) ?>')"><?= AifinHelper::SIGN_TRASH_2X ?></a>
    <a class="text-primary pointer hec-button pull-right m-r-10" onclick="editPost()"><?= AifinHelper::SIGN_MODIFY_2X ?></a>

    <div class="clearfix"></div>
    <hr>

    <div id="content" class="col-sm-12 m-t-30 m-l-20">
        <?= AifinHelper::processBBSPost($model->content) ?>
    </div>
    <div class="tags">
        <?php if (count($bbsPostTags = $model->bbsPostTags) > 0) { ?>
            <?php foreach ($bbsPostTags as $bbsPostTag) { ?>
                <a href="<?= Url::to(['bbs/post-by-tag']) . "?id=" . $bbsPostTag->idtag ?>" class="post-tag"><?= $bbsPostTag->bbsTag->tagname ?></a>
            <?php } ?>
        <?php } ?>
    </div>

    <div class="clearfix"></div>
    <div class="wis-numbers">
        <span><i class="zmdi zmdi-eye"></i> <?= $model->views ?></span>
    </div>
    <div class="clearfix"></div>
    <hr>

    <div class="col-sm-12 text-center m-t-30">
<!--        <h3><?= WebLang::t('article_useful') ?></h3>
        <a href="#" class="btn btn-outline-color"><?= WebLang::t('yes1') ?></a>
        <a href="#" class="btn btn-outline-color"><?= WebLang::t('no1') ?></a>-->
        <!--<p class=""><?= WebLang::t('have_more_questions') ?> <a href="#"><?= WebLang::t('leave_a_comment') ?></a></p>-->
    </div>

    <!-- Comments -->
    <div class="col-sm-offset-1 col-sm-11">
        <?php Pjax::begin(['id' => 'comments']); ?>
        <?php if (count($model->replies) > 0) { ?>

            <h4><?= WebLang::t('user') . WebLang::sp() . WebLang::t('comment') ?></h4>
            <?php
            echo $this->render('../bbs/_replies', [
                'model' => $model,
            ]);
            ?>
        <?php } ?>

        <h4><?= WebLang::t('leave_a_comment') ?></h4>
        <?php $form = ActiveForm::begin(['id' => 'article-comment', 'action' => Url::to(['knowledge/article-comment'])]); ?>
        <div class="col-sm-12 m-t-20 m-b-25">
            <input type="hidden" name="id" value="<?= $model->id ?>">
            <input type="hidden" name="token" value="<?= AifinHelper::genSingingToken(['id' => (int) $model->id]) ?>">
            <textarea class="form-control" rows="5" name="comments"></textarea>
        </div>
        <button class="btn btn-info m-t-20" type="button" id="submit-comment"><?= WebLang::t('submit') ?></button>
        <?php ActiveForm::end(); ?>
        <?php Pjax::end(); ?>
    </div>
</div>

<div class="col-md-3 col-sm-12">
    <?php if ($relatedPosts = $model->relatedPosts) { ?>
        <div class="col-sm-12 m-t-30">
            <h2 class="text-info lead"><?= WebLang::t('related_articles') ?></h2>
            <ul>
                <?php foreach ($relatedPosts as $relatedPost) { ?>
                    <li><a href="<?= Url::to(['bbs/board']) . "?id={$relatedPost->idboard}&pid=" . $relatedPost->id ?>"><?= $relatedPost->subject ?></a></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>
</div>

<div class="modal fade in" id="postModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 90%;height: 90%;">
        <div class="modal-content" style="height: 100%;">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button"><?= AifinHelper::SIGN_CLOSE ?></button>
                <h3 class="modal-title text-warning"><?= WebLang::t('post') ?></h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'post-form', 'enableClientValidation' => true]) ?>
            <input type="hidden" name="id" value="<?= $model->id ?>">
            <input type="hidden" name="uuid" id='uuid' value="">
            <input type="hidden" name="idboard" id='idboard' value="<?= $model->idboard ?>">

            <div class="modal-body">
                <div class="row">
                    <div id="subject-wrapper">
                        <div class="col-sm-12">
                            <div class='form-group'>
                                <div class='fg-line'>
                                    <label for='subject'><?= WebLang::t('subject') ?></label>
                                    <input type='text' name='subject' id='subject' class='form-control' value="<?= htmlspecialchars($model->subject) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="html-editor-click"><?= $model->content ?></div>
                    </div>


                    <div class="col-sm-6">
                        <div class='form-group'>
                            <div class='fg-line'>
                                <label for='subject'><?= WebLang::t('slug') ?></label>
                                <input type='text' name='slug' id='subject' class='form-control' value="<?= htmlspecialchars($model->slug) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div id="post-tag-wrapper">
                            <?php
                            $data = [
                                "red" => "red",
                                "green" => "green",
                                "blue" => "blue",
                                "orange" => "orange",
                                "white" => "white",
                                "black" => "black",
                                "purple" => "purple",
                                "cyan" => "cyan",
                                "teal" => "teal"
                            ];
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-warning" type="button"> <?= WebLang::t('cancel') ?></button>
                <button class="btn btn-sm btn-primary waves-effect form-submit" type="button"><?= WebLang::t('submit') ?></button>
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
    function sendKbFile(editorTag, file, editor, welEditable) {
        data = new FormData();
        data.append("file", file);

        $.ajax({
            url: "/backend/knowledge/post-upload",
            data: data,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            type: 'POST',
            success: function (response) {
                if (response.success) {
                    $.each(response.filename, function (k, v) {
                        $(editorTag).summernote("insertImage", v, 'filename');
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    }

    function editPost() {
        $('.html-editor-click').summernote({
            focus: true,
            height: 300,
            minHeight: 300,
            callbacks: {
                onImageUpload: function (files, editor, $editable) {
                    sendKbFile('.html-editor-click', files[0], editor, $editable);
                },
                onPaste: function (e) {
                    console.log('paste');
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');

                    e.preventDefault();

                    // Firefox fix
                    setTimeout(function () {
                        document.execCommand('insertText', false, bufferText);
                    }, 10);
                }
            }
        });

        $("#postModal .modal-title").html('<?= Weblang::t('update') ?>');
        $("#postModal").modal({backdrop: 'static', keyboard: false});
    }

    $('body').on('click', '#submit-comment', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= Url::to(['/knowledge/article-comment']) ?>',
            cache: false,
            dataType: 'json',
            type: 'POST',
            data: $('#article-comment').serialize(),
            error: function (xhr, ajaxOptions, thrownError) {
                swal(t.failure, t.ajax_error, "error");
            },
            success: function (response) {
                if (response.success) {
                    notify(t.success, 'success');
                    $.pjax.reload('#comments', {timeout: false, url: ajaxUrl});
                } else {
                    swal(t.failure, response.msg, "error");
                }
            }
        });
    });

    //Save
    $('body').on('click', '#post-form .form-submit', function () {
        var markupStr = $(".html-editor-click").summernote('code');

        data = $('#post-form').serializeArray();
        data.push({name: "post_content", value: markupStr});

        $.ajax({
            url: '<?= Url::to(['/knowledge/post']) ?>?id=<?= $model->id ?>',
                        cache: false,
                        dataType: 'json',
                        type: 'POST',
                        data: data,
                        error: function (xhr, ajaxOptions, thrownError) {
                            swal({title: t.failure, text: t.ajax_error, type: "error", html: false});
                        },
                        success: function (response) {
                            if (response.success) {
                                swal({title: t.success, type: "success"
                                },
                                        function () {
                                            location.reload();
                                        });
                            } else {
                                swal({title: t.failure, text: response.msg, type: "error", html: true});
                            }
                        }
                    });
                });

                // sticky
                $('body').on('click', '.toggle-sticky', function () {
                    $.ajax({
                        url: '<?= Url::to(['/knowledge/article-sticky']) ?>',
                        cache: false,
                        dataType: 'json',
                        type: 'POST', data: {
                            id: <?= $model->id ?>,
                            token: '<?= AifinHelper::genSingingToken() ?>'
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            swal(t.failure, t.ajax_error, "error");
                        },
                        success: function (response) {
                            if (response.success) {
                                swal({title: t.success, type: "success"
                                },
                                        function () {
                                            location.reload();
                                        });
                            } else {
                                swal(t.failure, response.msg, "error");
                            }
                        }
                    });
                });

                function deletePost(id, token) {
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
                                url: '<?= Url::to(['knowledge/post-delete']) ?>?id=' + id,
                                cache: false,
                                dataType: 'json',
                                type: 'POST',
                                data: {
                                    token: token
                                },
                                error: function (xhr, ajaxOptions, thrownError) {
                                    swal(t.failure, t.ajax_error, "error");
                                },
                                success: function (response) {
                                    if (response.success) {
                                        swal({
                                            title: t.success, type: "success"
                                        }, function () {
                                            window.location = "<?= Url::to(['knowledge/board']) . "?id=" . $model->idboard ?>";
                                        });
                                    } else {
                                        swal(t.failure, response.msg, "error");
                                    }
                                }
                            });
                        }
                    });
                }

</script>
<?php JSRegister::end(); ?>
