<?php

use common\helpers\AifinHelper;
use common\helpers\IfapFormHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\WebLang;
use yii\widgets\Pjax;
?>

<?php if (count($docs) > 0) { ?>
    <button class="btn btn-info m-r-15 pull-right" id="checkAll"><?= WebLang::t('select_all') ?></button>
<?php } ?>
<h2 class="lead text-primary">
    <?= WebLang::t('files') ?>
</h2>

<?php $form = ActiveForm::begin(['id' => 'bulk-action-form']) ?>

<?php if (count($docs) > 0 && $docs) { ?>
    <?php foreach ($docs as $row) { ?>
        <div class="col-md-6 col-sm-12">
            <div class="dms-item dms-item-file">
                <div class="checkbox pull-right m-t-0">
                    <label class='control-label'>
                        <input type='checkbox' name='iddoc' value='<?= $row['id'] ?>' id="<?= $row['token'] ?>">
                        <i class='input-helper'></i>
                    </label>
                </div>

                <div class="pull-left m-r-10">
                    <?php if ($row->checkFileExist()) { ?>
                        <?= $row->filetypeIcon ?>
                    <?php } else { ?>
                        <img class="dms-preview" src="<?= Url::to(['/']) ?>/images/filetypes/unknown.jpg">
                    <?php } ?>
                </div>
                <div class="pull-right m-t-10">
                    <?= $row['locked'] == 1 ? AifinHelper::icon("text-danger zmdi-hc-2x", AifinHelper::SIGN_LOCKED, 'locked') : '' ?>
                    <?= $row['checked_out_idpeople'] > 0 ? AifinHelper::icon("text-info zmdi-hc-2x", AifinHelper::SIGN_CHECKOUT, WebLang::t('checked_out_by')) . "<br>" . $row->checkedOutPeople->name() : '' ?>
                </div>
                <?php if (!$row->checkFileExist()) { ?>
                    <span class="uppercase text-warning"><strong>(<?= WebLang::t('offline') ?>)</strong></span><br>
                <?php } ?>

                <span class="text-default f-700"><?= $row["doc_desc_$lang"] ?></span>
                <br><small><?= AifinHelper::datetime($row['created_at']) ?></small>

                <div class="toolbar">
                    <div class="icons">
                        <div class="pull-right">
                            <?php if ($row->checkFileExist()) { ?>
                                <a class='text-success pointer m-r-5'
                                   onclick="downloadDocument(<?= $row['id'] ?>, '<?= $row['token'] ?>');"><?= AifinHelper::icon('text-primary zmdi-hc-2x', AifinHelper::SIGN_DOWNLOAD, 'download') ?>
                                </a>
                            <?php } ?>

                            <?php if ($row['checked_out_idpeople'] == null || $row['checked_out_idpeople'] == Yii::$app->user->identity->idpeople) { ?>
                                <a class='text-warning pointer m-r-5' 
                                   onclick="editDoc(<?= $row['id'] ?>, '<?= $row['token'] ?>');"><?= AifinHelper::icon('text-warning zmdi-hc-2x', AifinHelper::SIGN_MODIFY, 'edit') ?>
                                </a>
                            <?php } ?>
                            <a class='text-danger pointer' 
                               onclick="deleteDoc(<?= $row['id'] ?>, '<?= $row['token'] ?>');"><?= AifinHelper::icon('text-danger zmdi-hc-2x', AifinHelper::SIGN_DELETE_1X, 'delete') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } else { ?>
    <p><?= WebLang::t('no_matching_record') ?></p>
<?php } ?>
<?php $form = ActiveForm::end() ?>
