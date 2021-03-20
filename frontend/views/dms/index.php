<?php

use common\helpers\AifinHelper;
use yii\helpers\Url;
use richardfan\widget\JSRegister;
use common\helpers\IfapFormHelper;
use yii\widgets\ActiveForm;
use common\models\WebLang;
use yii\widgets\Pjax;
use yii\helpers\Html;

//use kartik\widgets\Select2;
//use yii\web\JsExpression;

$model = new common\models\Document;

// for create form
$documentFolderModel = new common\models\DocumentFolder();
$documentFolderModel->is_public = 1;

//backend\assets\FileInputAsset::register($this);
//common\assets\DropZoneAsset::register($this);

$lang = Yii::$app->language;

if ($folder) {
    $token = AifinHelper::genSingingToken(['id' => (int) $folder->id]);
} else {
    $token = null;
}

$ajaxUrl = \yii\helpers\Url::to(['dms/index']) . "?f=$f&token=$token";
?>
<script>var ajaxUrl = "<?= $ajaxUrl ?>";</script>

<div class="col-sm-12">

    <?= Html::csrfMetaTags() ?>
    <?php if ($error != NULL): ?>
        <h2 class="text-danger lead"><?= AifinHelper::SIGN_ALERT ?> <?= $error ?></h2>
    <?php else: ?>
        <?php
        $this->params['breadcrumbs'][] = ['label' => WebLang::t('documents'), 'url' => ['/dms/index']];
        foreach ((array) $folderHistory as $i => $value)
            $this->params['breadcrumbs'][] = ['label' => $value['name'], 'url' => ['/dms/index', 'f' => $value['id']]];
        ?>

        <div class="list-group lg-odd-black">
            <div class="pull-right">
                <ul class="actions">
                    <li class="hidden" id="bulkActionBtn">
                        <a class="pointer" onclick="bulkActionModal();">
                            <i class="zmdi zmdi-wrench c-amber"></i>
                        </a>
                    </li>

                    <?php if (Yii::$app->request->isGet) { ?>
                        <li>
                            <a class="pointer" onclick="uploadModal();">
                                <i class="zmdi zmdi-upload"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="pointer" data-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                                <i class="zmdi zmdi-more-vert"></i>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a class="pointer" 
                                       onclick="createFolder(<?= $f ?>);"><?= WebLang::t('create_folder') ?></a>
                                </li>
                                <li>
                                    <a class="pointer" 
                                       onclick="deleteFolder(<?= $f ?>);"><?= WebLang::t('delete_folder') ?></a>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div class="row">

            <?php Pjax::begin(['id' => 'contents']); ?>
            <div class="col-lg-3 col-md-3 col-sm-12">
                <?=
                $this->render('_folders', [
                    'folders' => $folders,
                    'idparent' => $idparent,
                    'lang' => $lang,
                ])
                ?>
            </div>

            <div class="col-md-6 col-sm-12">
                <?php if ($folder) { ?>
                    <?php if ($folder->content) { ?>
                        <h2 class="lead text-primary"><?= WebLang::t('description') ?></h2>
                        <div class="padding">
                            <p class="reading"><?= isset($folder->content) ? ebr($folder->content) : '' ?></p>
                        </div>
                    <?php } ?>
                <?php } ?>

                <?=
                $this->render('_files', [
                    'folders' => $folders,
                    'docs' => $docs,
                    'idparent' => $idparent,
                    'lang' => $lang,
                ])
                ?>
            </div>
            <?php Pjax::end(); ?>

            <div class="col-md-3 col-sm-12">
                <div id="permission">
                    <?php Pjax::begin(['id' => 'permission']); ?>
                    <?=
                    $this->render('_permission', [
                        'folder' => $folder,
                        'f' => $f,
                        'idparent' => $idparent,
                        'lang' => $lang,
                        'q' => $q,
                    ])
                    ?>
                    <?php Pjax::end(); ?>
                </div>
                <div class="col-sm-12">
                    <?php
                    $form = ActiveForm::begin([
                                'id' => 'myDropzone',
                                'action' => Url::to(['/dms/upload']),
                                'options' => [
                                    'class' => 'dropzone'
                                ]
                            ])
                    ?>
                    <div class="fallback">
                        <input name="file" type="file" multiple>
                    </div>
                    <input name="idfolder" type="hidden" value="<?= $f ?>">
                    <?php ActiveForm::end() ?>

                    <?= ''/*
                      \common\assets\dropzone\DropZone::widget([
                      'options' => [
                      'url' => Url::to(['/dms/upload']),
                      ],
                      'clientEvents' => [
                      'success' => 'function(file, filePath){
                      }',
                      'sending' => 'function(file, xhr, formData){
                      formData.append("idfolder", ' . $f . ');
                      }'
                      ],
                      ])
                     * 
                     */
                    ?>
                </div>
            </div>

            <div class="clearfix"><span id="form-spinner"></span></div>
            <div id="ajax-response" class="alert alert-danger" role="alert" style="display:none;">
                <span id="responsemsg"></span>
            </div>
        </div>
    </div>

    <!-- Create folder modal -->
    <div class="modal fade modal-dialog-center " id="createFolderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content-wrap">
                <div class="modal-content">
                    <div class="modal-header">
                        <button aria-hidden="true" data-dismiss="modal" class="close"
                                type="button"><?= AifinHelper::SIGN_CLOSE ?></button>
                        <h3 class="modal-title text-uppercase text-warning"><i
                                class="zmdi zmdi-plus zmdi-hc-2x text-danger"></i> <?= WebLang::t('create_folder') ?>
                        </h3>
                    </div>
                    <div class="modal-body">
                        <?php $form = ActiveForm::begin(['id' => 'createFolderForm', 'options' => ['enctype' => 'multipart/form-data']]) ?>
                        <input type="hidden" id="documentfolder-idparent" name="DocumentFolder[idparent]" value="<?= $f ?>">
                        <input type="hidden" id="cf-a" name="a" value="0">
                        <input type="hidden" id="documentfolder-id" name="id" value="0">
                        <div class="row">
                            <div class="col-sm-12">
                                <?= IfapFormHelper::genTextArea($form, $documentFolderModel, "folder_name_eng")->label(WebLang::t('eng')); ?>
                            </div>
                            <div class="col-sm-12">
                                <?= IfapFormHelper::genTextArea($form, $documentFolderModel, "folder_name_cht")->label(WebLang::t('cht')); ?>
                            </div>
                            <div class="col-sm-12">
                                <?= IfapFormHelper::genTextArea($form, $documentFolderModel, "folder_name_chs")->label(WebLang::t('chs')); ?>
                            </div>
                            <div class="col-sm-12">
                                <?= IfapFormHelper::genTextArea($form, $documentFolderModel, "folder_name_jap")->label(WebLang::t('jap')); ?>
                            </div>
                            <div class="col-xs-12">
                                <?= IfapFormHelper::genCheckboxCustomValue(WebLang::t('public'), "documentfolder-is_public", "DocumentFolder[is_public]", 1, (isset($folder->parentFolder) ? $folder->parentFolder->is_public : 0)) ?>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <?= IfapFormHelper::genCheckboxCustomValue(WebLang::t('tree_access'), "documentfolder-tree_access", "DocumentFolder[tree_access]", 1, ($folder ? $folder->tree_access : 0));
                                ?>
                            </div>
                        </div>

                        <div class="clearfix"><span id="form-spinner"></span></div>
                        <div class="alert alert-danger" role="alert" style="display:none;">
                            <span id="createFolderForm-msg"></span>
                        </div>

                        <div class="modal-footer">
                            <button data-dismiss="modal" class="btn btn-warning"
                                    type="button"> <?= WebLang::t('cancel') ?></button>
                            <button data-dismiss="modal" class="btn btn-info" id="submit" name="submit"
                                    onclick="ajaxCreateFolder(this.form);"
                                    type="button"> <?= WebLang::t('save') ?></button>
                        </div>
                        <?php ActiveForm::end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($folder) { ?>    
        <!-- upload Document modal -->
        <div class="modal fade modal-dialog-center " id="uploadModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content-wrap">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button aria-hidden="true" data-dismiss="modal" class="close"
                                    type="button"><?= AifinHelper::SIGN_CLOSE ?></button>
                            <h2 class="modal-title text-uppercase text-warning lead"><?= WebLang::t('upload_file') ?>
                                (<?= AifinHelper::file_upload_max_size("kb"); ?>kb <?= WebLang::t('maximum') ?>)</h2>
                        </div>
                        <div class="modal-body">
                            <?php $form = ActiveForm::begin(['id' => 'uploadForm', 'options' => ['enctype' => 'multipart/form-data', 'accept-charset' => "UTF-8"]]) ?>
                            <input type="hidden" id="idfolder" name="idfolder" value="<?= $f ?>">
                            <input type="hidden" name="MAX_FILE_SIZE"
                                   value="<?= AifinHelper::file_upload_max_size("byte"); ?>"/>

                            <div class="col-sm-12">
                                <div class="col-xs-12 fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-preview thumbnail" data-trigger="fileinput"
                                         style="margin-top:5px;width:100%;"></div>
                                    <div>
                                        <span class="btn btn-info btn-file">
                                            <span class="fileinput-new"><?= WebLang::t('select') ?></span>
                                            <span class="fileinput-exists"><?= WebLang::t('update') ?></span>
                                            <input type="file" name="file[]" class="inputimage">
                                        </span>
                                        <a  class="btn btn-danger fileinput-exists" data-dismiss="fileinput"><?= WebLang::t('delete') ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <?= IfapFormHelper::genTextArea($form, $model, "doc_desc_eng")->label(WebLang::t('eng')); ?>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <?= IfapFormHelper::genTextArea($form, $model, "doc_desc_cht")->label(WebLang::t('cht')); ?>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <?= IfapFormHelper::genTextArea($form, $model, "doc_desc_chs")->label(WebLang::t('chs')); ?>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <?= IfapFormHelper::genTextArea($form, $model, "doc_desc_jap")->label(WebLang::t('jap')); ?>
                                </div>
                            </div>

                            <div class="clearfix"><span id="upload-form-spinner"></span></div>
                            <div id="upload-ajax-response" class="alert alert-danger" role="alert"
                                 style="display:none;">
                                <span id="upload-responsemsg"></span>
                            </div>

                            <div class="modal-footer">
                                <button data-dismiss="modal" class="btn btn-warning"
                                        type="button"> <?= WebLang::t('cancel') ?></button>
                                <button class="btn btn-info" id="submit" name="submit" onclick="ajaxUpload(this.form);"
                                        type="button"> <?= WebLang::t('save') ?></button>
                            </div>
                            <?php ActiveForm::end() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Doc modal -->
        <div class="modal fade modal-dialog-center " id="editDocModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content-wrap">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button aria-hidden="true" data-dismiss="modal" class="close"
                                    type="button"><?= AifinHelper::SIGN_CLOSE ?></button>
                            <h2 class="modal-title text-uppercase text-info lead"><i
                                    class="zmdi zmdi-edit zmdi-hc-42 text-danger"></i> <?= WebLang::t('document') . WebLang::sp() . WebLang::t('edit') ?>
                            </h2>
                        </div>
                        <div class="modal-body">

                            <form id="editDocForm" name="editDocForm" method="POST" enctype="multipart/form-data">
                                <input type="hidden" id="id" name="id" value="0">
                                <input type="hidden" id="a" name="a" value="0">
                                <input type="hidden" id="idfolder" name="idfolder" value="<?= $f ?>">
                                <input type="hidden" name="MAX_FILE_SIZE"
                                       value="<?= AifinHelper::file_upload_max_size("byte") ?>"/>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <?= IfapFormHelper::genTextArea($form, $model, "doc_desc_eng")->label(WebLang::t('eng')); ?>
                                    </div>
                                    <div class="col-sm-12">
                                        <?= IfapFormHelper::genTextArea($form, $model, "doc_desc_cht")->label(WebLang::t('cht')); ?>
                                    </div>
                                    <div class="col-sm-12">
                                        <?= IfapFormHelper::genTextArea($form, $model, "doc_desc_chs")->label(WebLang::t('chs')); ?>
                                    </div>
                                    <div class="col-sm-12">
                                        <?= IfapFormHelper::genTextArea($form, $model, "doc_desc_jap")->label(WebLang::t('jap')); ?>
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-12">
                                    <h2 class="lead text-uppercase text-info"><?= WebLang::t('attachment') ?>
                                        (<?= AifinHelper::file_upload_max_size("kb"); ?>
                                        kb <?= WebLang::t('maximum') ?>)</h2>
                                </div>

                                <div class="col-md-6 col-xs-12">
                                    <div class="col-xs-12 fileinput fileinput-new" data-provides="fileinput">
                                        <input type="text" name="orig_file_name" id="orig_file_name" class="form-control"
                                               disabled><br>
                                        <div class="fileinput-preview thumbnail" data-trigger="fileinput"
                                             style="margin-top:5px;width:100%;"></div>
                                        <div>
                                            <span class="btn btn-info btn-file">
                                                <span class="fileinput-new"><?= WebLang::t('select') ?></span>
                                                <span class="fileinput-exists"><?= WebLang::t('update') ?></span>
                                                <input type="file" name="files[]" class="inputimage">
                                            </span>
                                            <a  class="btn btn-danger fileinput-exists" data-dismiss="fileinput">Remove</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="clearfix"><span id="form-spinner"></span></div>
                                <div id="ajax-response" class="alert alert-danger" role="alert" style="display:none;">
                                    <span id="responsemsg"></span>
                                </div>

                                <div class="modal-footer">
                                    <button data-dismiss="modal" class="btn btn-warning"
                                            type="button"> <?= WebLang::t('cancel') ?></button>
                                    <button data-dismiss="modal" class="btn btn-info" id="submit" name="submit"
                                            onclick="ajaxEditDoc(this.form);"
                                            type="button"> <?= WebLang::t('save') ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Folder modal -->
        <div class="modal fade modal-dialog-center " id="editModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content-wrap">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button aria-hidden="true" data-dismiss="modal" class="close"
                                    type="button"><?= AifinHelper::SIGN_CLOSE ?></button>
                            <h3 class="modal-title text-uppercase"><i
                                    class="zmdi zmdi-edit zmdi-hc-2x text-warning"></i> <?= WebLang::t('folder') . WebLang::sp() . WebLang::t('edit') ?>
                            </h3>
                        </div>
                        <div class="modal-body">
                            <form id="editFolderForm" name="editFolderForm" method="POST">
                                <input type="hidden" id="id" name="id" value="<?= $folder->id ?>">
                                <input type="hidden" id="a" name="a" value="2">
                                <input type="hidden" id="documentfolder-idparent" name="DocumentFolder[idparent]" value="<?= $folder->idparent ?>">
                                <input type="hidden" id="documentfolder-is_public" name="DocumentFolder[is_public]" value="0">

                                <div class="row">
                                    <div class="col-xs-12">
                                        <?= IfapFormHelper::genTextArea($form, $folder, "folder_name_eng", WebLang::t('eng')); ?>
                                    </div>
                                    <div class="col-xs-12">
                                        <?= IfapFormHelper::genTextArea($form, $folder, "folder_name_cht", WebLang::t('cht')); ?>
                                    </div>
                                    <div class="col-xs-12">
                                        <?= IfapFormHelper::genTextArea($form, $folder, "folder_name_chs", WebLang::t('chs')); ?>
                                    </div>
                                    <div class="col-xs-12">
                                        <?= IfapFormHelper::genTextArea($form, $folder, "folder_name_jap", WebLang::t('jap')); ?>
                                    </div>
                                    <div class="col-xs-12">
                                        <?= IfapFormHelper::genTextArea($form, $folder, "content", WebLang::t('description')); ?>
                                    </div>
                                    <div class="col-xs-12">
                                        <?= IfapFormHelper::genCheckboxCustomValue(WebLang::t('public'), "documentfolder-is_public", "DocumentFolder[is_public]", 1, $folder->is_public) ?>

                                    </div>
                                </div>

                                <div class="clearfix"><span id="form-spinner"></span></div>
                                <div id="ajax-response" class="alert alert-danger" role="alert" style="display:none;">
                                    <span id="responsemsg"></span>
                                </div>

                                <div class="modal-footer">
                                    <button data-dismiss="modal" class="btn btn-warning"
                                            type="button"> <?= WebLang::t('cancel') ?></button>
                                    <button data-dismiss="modal" class="btn btn-info" id="submit" name="submit"
                                            onclick="ajaxEditFolder(this.form);"
                                            type="button"> <?= WebLang::t('save') ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Action modal -->
        <div class="modal fade modal-dialog-center " id="bulkActionModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content-wrap">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button aria-hidden="true" data-dismiss="modal" class="close" type="button"><?= AifinHelper::SIGN_CLOSE ?></button>
                            <h2 class="modal-title text-uppercase text-info lead"><i class="zmdi zmdi-edit zmdi-hc-42 text-danger"></i> <?= WebLang::t('bulk_action') ?></h2>
                        </div>
                        <div class="modal-body">
                            <?php $form = ActiveForm::begin(['id' => 'bulkActionModalForm']) ?>
                            <!--<form id="bulkActionModalForm" name="bulkActionModalForm" method="POST">-->
                            <input type="hidden" id="idfolder" name="idfolder" value="<?= $f ?>">

                            <div class="row">
                                <div class="col-sm-12">
                                    <?=
                                    IfapFormHelper::genSelectField(WebLang::t('action'), "a", "a", [
                                        '1' => WebLang::t('lock'),
                                        '2' => WebLang::t('update'),
                                        '3' => WebLang::t('delete'),
                                        '4' => WebLang::t('checkout'),
                                        '5' => WebLang::t('unlock'),
                                        '6' => WebLang::t('checkin'),
                                        '7' => WebLang::t('move'),
                                            ], 2, 0)
                                    ?>
                                </div>
                            </div>

                            <div class="clearfix"><span id="form-spinner"></span></div>            
                            <div id="ajax-response" class="alert alert-danger" role="alert" style="display:none;">
                                <span id="responsemsg"></span>
                            </div>

                            <div class="modal-footer">
                                <button data-dismiss="modal" class="btn btn-warning" type="button"> <?= WebLang::t('cancel') ?></button>
                                <button data-dismiss="modal" class="btn btn-info" id="submit" name="submit" onclick="ajaxBulkAction('bulkActionModalForm');" type="button"> <?= WebLang::t('save') ?></button>
                            </div>		
                            <?php ActiveForm::end() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
<?php endif; ?>

<?php
JSRegister::begin([
    'position' => \yii\web\View::POS_END
]);
?>
<script>
    function selectFolder(id, src, token) {
        window.location.href = '<?= Url::to(['/dms/index']) . "?f=" ?>' + id + '&src=' + src + "&token=" + token;
    }

    function deleteDoc(id, token) {
        swal({
            title: t.are_you_sure,
            text: t.unrecoverable_warning,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: t.confirm + t.sp + t.delete,
            cancelButtonText: t.no_dont,
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                ajaxDocDelete(id, token);
            }
        });
    }

    function ajaxDocDelete(id, token) {
        $.ajax({
            url: '<?= Url::to(['/dms/delete']) ?>',
            cache: false,
            dataType: 'json',
            type: 'POST',
            data: {
                id: id,
                token: token
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $('#form-spinner').html('');
                swal(t.failure, t.ajax_error, "error");
            },
            success: function (response) {
                $('#form-spinner').html('');
                if (response.success) {
                    $.pjax.reload('#contents', {timeout: false, url: ajaxUrl});
                } else {
                    swal({
                        html: true,
                        title: t.failure,
                        text: response.msg,
                        type: "error",
                        allowOutsideClick: false
                    });
                }
            }
        });
    }

    function ajaxToggleLock(id, token) {
        $.ajax({
            url: '<?= Url::to(['/dms/lock-doc']) ?>',
            cache: false,
            dataType: 'json',
            type: 'POST',
            data: {
                id: id,
                token: token
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $('#form-spinner').html('');
                swal(t.failure, t.ajax_error, "error");
            },
            success: function (response) {
                $('#form-spinner').html('');
                if (response.success) {
                    $.pjax.reload('#contents', {timeout: false, url: ajaxUrl});
                } else {
                    swal({
                        html: true,
                        title: t.failure,
                        text: response.msg,
                        type: "error",
                        allowOutsideClick: false
                    });
                }
            }
        });
    }

    function ajaxToggleCheckout(id, token) {
        $.ajax({
            url: '<?= Url::to(['/dms/checkout-doc']) ?>',
            cache: false,
            dataType: 'json',
            type: 'POST',
            data: {
                id: id,
                token: token
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $('#form-spinner').html('');
                swal(t.failure, t.ajax_error, "error");
            },
            success: function (response) {
                $('#form-spinner').html('');
                if (response.success) {
                    $.pjax.reload('#contents', {timeout: false, url: ajaxUrl});
                } else {
                    swal({
                        html: true,
                        title: t.failure,
                        text: response.msg,
                        type: "error",
                        allowOutsideClick: false
                    });
                }
            }
        });
    }

    function uploadModal() {
        $('#uploadForm')[0].reset();
        $('#uploadForm input[name=a]').val(1);
        $("#uploadModal").modal({backdrop: 'static', keyboard: false});
    }

    function editDocModal() {
        $('#editDocForm')[0].reset();
        $('#editDocForm input[name=a]').val(1);
        $("#editDocModal").modal({backdrop: 'static', keyboard: false});
    }

    function editFolder(id) {
        $('#editFolderForm')[0].reset();
        $("#editModal").modal({backdrop: 'static', keyboard: false});
    }

    function ajaxEditFolder(id) {
        data = $('#editFolderForm').serialize();

        $.ajax({
            url: '<?= Url::to(['/dms/process-doc-folder']) ?>',
            cache: false,
            dataType: 'json',
            type: 'POST',
            data: data,
            error: function (xhr, ajaxOptions, thrownError) {
                $('#form-spinner').html('');
                swal(t.failure, t.ajax_error, "error");
            },
            success: function (response) {
                $('#form-spinner').html('');
                if (response.success) {
                    swal({
                        title: t.success,
                        text: "",
                        type: "success",
                        allowOutsideClick: false
                    },
                            function () {
                                $.pjax.reload('#permission', {timeout: false, url: ajaxUrl});
                            }
                    );
                } else {
                    swal({
                        html: true,
                        title: t.failure,
                        text: response.msg,
                        type: "error",
                        allowOutsideClick: false
                    });
                }
            }
        });
    }

    function editDoc(id) {
        $('#editDocForm')[0].reset();
        $("#editDocModal").modal({backdrop: 'static', keyboard: false});
        $.ajax({
            url: '<?= Url::to(['/dms/process-doc']) ?>',
            cache: false,
            dataType: 'json',
            type: 'GET',
            data: {
                id: id,
                a: 0
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $('#form-spinner').html('');
                swal(t.failure, t.ajax_error, "error");
            },
            success: function (response) {
                var data = response.data;
                $('#ajax-response').hide();
                $('#form-spinner').html('');
                $('#editDocForm')[0].reset();
                $("#editDocForm #id").val(data.id);
                $("#editDocForm #idfolder").val(data.idfolder);
                $("#editDocForm #document-doc_desc_eng").val(data.doc_desc_eng);
                $("#editDocForm #document-doc_desc_cht").val(data.doc_desc_cht);
                $("#editDocForm #document-doc_desc_chs").val(data.doc_desc_chs);
                $("#editDocForm #document-doc_desc_jap").val(data.doc_desc_jap);
                $("#editDocForm #idprovider").val(data.idprovider);
                $("#editDocForm #idproduct_cat").val(data.idproduct_cat);
                $("#editDocForm #orig_file_name").val(data.orig_file_name);
                $('#editDocForm input[name=a]').val(2);
                //                                            }
            }
        });
    }

    function ajaxEditDoc(form) {
        var data = new FormData(form);
        $('#editDocForm').validate();
        $.ajax({
            url: '<?= Url::to(['/dms/process-doc']) ?>',
            cache: false,
            dataType: 'json',
            type: 'POST',
            data: data,
            processData: false,
            contentType: false,
            error: function (xhr, ajaxOptions, thrownError) {
                $('#form-spinner').html('');
                swal(t.failure, t.ajax_error, "error");
            },
            success: function (response) {
                $('#form-spinner').html('');
                if (response.success) {
                    swal({
                        title: t.success,
                        text: "",
                        type: "success",
                        allowOutsideClick: false
                    },
                            function () {
                                //                                window.location.reload();
                                //                                $('#editDocForm').modal('toggle');
                                $.pjax.reload('#contents', {timeout: false, url: ajaxUrl});
                            });
                } else {
                    swal({
                        html: true,
                        title: t.failure,
                        text: response.msg,
                        type: "error",
                        allowOutsideClick: false
                    });
                }
            }
        });
    }


    $.validator.addMethod('filesize', function (value, element, param) {
        // param = size (in bytes)
        // element = element to validate (<input>)
        // value = value of the element (file name)
        return this.optional(element) || (element.files[0].size <= param)
    });

    function ajaxUpload(form) {
        var data = new FormData(form);
        $('#uploadForm').validate({
            rules: {inputimage: {required: true, accept: "png|jpe?g|gif", filesize: <?= AifinHelper::file_upload_max_size('byte') ?>}},
            messages: {inputimage: "File must be JPG, GIF or PNG, less than 1MB"}
        });

        if ($('#uploadForm').valid()) {
            $('#upload-form-spinner').html('<img src="<?= Url::to(['/images/input-spinner.gif']) ?>">');

            $.ajax({
                url: '<?= Url::to(['/dms/upload']) ?>',
                cache: false,
                dataType: 'json',
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                error: function (xhr, ajaxOptions, thrownError) {
                    $('#upload-form-spinner').html('');
                    swal(t.failure, t.ajax_error, "error");
                },
                success: function (response) {
                    $('#upload-form-spinner').html('');
                    if (response.success) {
                        swal({
                            title: t.success,
                            text: "",
                            type: "success",
                            allowOutsideClick: false
                        },
                                function () {
                                    $('#uploadModal').modal('hide');
                                    $.pjax.reload('#contents', {timeout: false, url: ajaxUrl});
                                }
                        );
                    } else {
                        if (response.msg) {
                            swal(t.failure, response.msg, "error");
                            //                        $('#upload-ajax-response').show();
                            //                        $('#upload-responsemsg').html(response.msg);
                        }
                    }
                }
            });
        }
    }

    function uploadModal() {
        $('#uploadForm')[0].reset();
        $('#uploadForm input[name=a]').val(1);
        $("#uploadModal").modal({backdrop: 'static', keyboard: false});
    }

    function deleteFolder(id) {
        swal({
            title: t.are_you_sure,
            text: t.unrecoverable_warning,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: t.confirm + t.sp + t.delete,
            cancelButtonText: t.no_dont,
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                ajaxDeleteFolder(id);
            } else {
                swal(t.cancelled, t.your_record_is_safe, "error");
            }
        });
    }

    function ajaxDeleteFolder(id) {
        $.ajax({
            url: '<?= Url::to(['/dms/process-doc-folder']) ?>',
            cache: false,
            dataType: 'json',
            type: 'POST',
            data: {
                id: id,
                a: 3
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $('#form-spinner').html('');
                swal(t.failure, t.ajax_error, "error");
            },
            success: function (response) {
                $('#form-spinner').html('');
                if (response.success) {
                    window.location.href = '<?= Url::to(['/dms/index', 'f' => $idparent]) ?>';
                } else {
                    swal({
                        html: true,
                        title: t.failure,
                        text: response.msg,
                        type: "error",
                        allowOutsideClick: false
                    });
                }
            }
        });
    }

    function createFolder(idparent) {
        $('#createFolderForm')[0].reset();
        $('#createFolderForm input[name=a]').val(1);
        $("#createFolderModal").modal({backdrop: 'static', keyboard: false});
    }

    function ajaxCreateFolder(form) {
        if ($('#createFolderForm').validate()) {

            data = $('#createFolderForm').serialize();
            $.ajax({
                url: '<?= Url::to(['/dms/process-doc-folder']) ?>',
                cache: false,
                dataType: 'json',
                type: 'POST',
                data: data,
                error: function (xhr, ajaxOptions, thrownError) {
                    $('#form-spinner').html('');
                    $('#createFolderForm-msg').val(response.msg);
                    return false;
                },
                success: function (response) {
                    $('#form-spinner').html('');
                    if (response.success) {
                        swal({
                            title: t.success,
                            text: "",
                            type: "success",
                            allowOutsideClick: false
                        },
                                function () {
                                    window.location.reload();
                                    //                                        $.pjax.reload('#folders', {timeout: false, url: ajaxUrl});
                                }
                        );
                    } else {
                        $('#createFolderForm-msg').val(response.msg);
                    }
                    return false;
                }
            });
        }
    }

    if ($('.lvh-search-trigger')[0]) {

        $('body').on('click', '.lvh-search-trigger', function (e) {
            e.preventDefault();
            x = $(this).closest('.lv-header-alt').find('.lvh-search');

            x.fadeIn(300);
            x.find('.lvhs-input').focus();
        });

        //Close Search
        $('body').on('click', '.lvh-search-close', function () {
            x.fadeOut(300);
            setTimeout(function () {
                x.find('.lvhs-input').val('');
            }, 350);
        })
    }

    $("input[name='iddoc[]']").change(function () {
        $('#bulkActionBtn').removeClass('hidden');
    });

    $('#editDocModal #idprovider').change(function () {
        $.ajax({
            type: "GET",
            url: "<?= Url::to(['/plan/get-product-by-provider']) ?>",
            data: {
                'idprovider': $('#editDocModal select[id=idprovider]').val(),
                'idproduct_cat': $('#editDocModal select[id=idproduct_cat]').val(),
            }, success: function (data) {
                $('#idproduct').html('');
                var opts = data.data;
                $('#idproduct').append('<option value=""><?= WebLang::t('please_select') ?></option>');
                $.each(opts, function (i, d) {
                    $('#idproduct').append('<option value="' + d.id + '">' + d.itemname + '</option>');
                });
            }
        });
    });

    $(document).ready(function () {
        $('body').on('click', '#checkAll', function () {
            $('form#bulk-action-form input:checkbox').each(function () {
                if (this.id != 'checkAll') {
                    if (!this.checked) {
                        $(this).prop('checked', true);
                        $('#bulkActionBtn').removeClass('hidden');
                    } else {
                        $(this).prop('checked', false);
                        $('#bulkActionBtn').addClass('hidden');
                    }
                }
            })
        });
    });

    $('body').on('click', 'form#bulk-action-form input:checkbox', function () {
        $('#bulkActionBtn').removeClass('hidden');
    });

    function bulkActionModal() {
        $("#bulkActionModal").modal({backdrop: 'static', keyboard: false});
    }

    function ajaxBulkAction(form) {
        data = $('#' + form).serialize();
        $('form#bulk-action-form input:checkbox').each(function () {
            if (this.name != 'checkAll') {
                console.log(this);
                if (this.checked) {
                    data += '&' + this.name + '[]=' + this.value;
                    data += '&token[]=' + this.id;
                }
            }
        })
        $.ajax({
            url: '<?= Url::to(['dms/process-bulk-action']) ?>',
            cache: false,
            dataType: 'json',
            type: 'POST',
            data: data,
            error: function (xhr, ajaxOptions, thrownError) {
                $('#bulk-action-modal #form-spinner').html('');
                swal(t.failure, t.ajax_error, "error");
            },
            success: function (response) {
                $('#bulk-action-modal #form-spinner').html('');
                if (response.success) {
                    swal({
                        title: t.success,
                        text: "",
                        type: "success",
                        allowOutsideClick: false
                    },
                            function () {
                                $.pjax.reload('#contents', {timeout: false, url: ajaxUrl});
                            });
                } else {
                    swal({
                        html: true,
                        title: t.failure,
                        text: response.msg,
                        type: "error",
                        allowOutsideClick: false
                    });
                }
            }
        });
    }

    Dropzone.options.myDropzone = {
        uploadMultiple: true,
        maxFilesize: <?= \common\helpers\AifinHelper::file_upload_max_size("mb") ?>, // mb
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
                $.pjax.reload('#contents', {timeout: false, url: ajaxUrl});
            });
            this.on("queuecomplete", function (file) {
                this.removeAllFiles(true);
            });
        }
    }
</script>
<?php JSRegister::end(); ?>