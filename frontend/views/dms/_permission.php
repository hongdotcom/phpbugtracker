<?php

use common\helpers\AifinHelper;
//use common\helpers\IfapFormHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\WebLang;
?>

<?php if ($folder) { ?>
    <div class="row">
        <div class="col-xs-12">
            <!-- search form -->
            <div class="m-b-20">
                <?php $form = ActiveForm::begin(['id' => 'searchDMS']) ?>
                <input id="q" name="q" type="text" placeholder=" <?= WebLang::t('search') ?> ..." class="form-control" value="<?= $q ?>">
                <?php $form->end(); ?>
            </div>

            <h3><?= trim($folder->name()) ?> <label class="label label-primary"><?= $folder->id ?></label>
                <a class="pointer text-warning" onclick="editFolder(<?= $f ?>);"> <?= AifinHelper::SIGN_MODIFY ?></a>
            </h3>

            <label class="label label-info m-r-10 f-12"><?= WebLang::t('public') ?></label>
            <?= $folder->is_public == 1 ? AifinHelper::SIGN_CHECK : AifinHelper::SIGN_CROSS ?>
            <br>

            <label class="label label-warning m-r-10 f-12"><?= WebLang::t('owner') ?></label>
            <span class="f-13"><?= $folder->idpeople_owner > 0 ? $folder->idpeople_owner : '--' ?></span>

            <?php if ($folder->idpeople_owner > 0) { ?>
                <br><label class="label label-danger m-r-10 f-13"><?= WebLang::t('tree_access') ?></label>
                <?= $folder->tree_access == 1 ? AifinHelper::SIGN_CHECK_1X : AifinHelper::SIGN_CROSS_1X ?>
            <?php } ?>

            <table class="table table-sm m-t-10">
                <tr>
                    <td class="f-13"><?= WebLang::t('updated_at') ?></td>
                    <td class="f-13"><?= $folder->updated_at ?></td>
                </tr>
                <tr>
                    <td class="f-13"><?= WebLang::t('created_at') ?></td>
                    <td class="f-13"><?= $folder->created_at ?></td>
                </tr>
            </table>
        </div>

    </div>
<?php } ?>
