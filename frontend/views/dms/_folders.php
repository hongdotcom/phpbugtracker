<?php

use common\helpers\IfapHelper;
use common\models\WebLang;
use common\models\People;
use yii\helpers\Url;
?>

<h2 class="lead text-primary"><?= WebLang::t('folders') ?></h2>
<?php if ($folders && count($folders) > 0) { ?>
    <?php foreach ($folders as $row) { ?>
        <?php $token = IfapHelper::genSingingToken(['id' => (int) $row->id]); ?>
        <div class="col-sm-12">
            <a class="pointer text-default f-700" onclick="selectFolder(<?= $row->id ?>, <?= $row->idparent ? $row->idparent : 'null' ?>, '<?= $token ?>');">
                <div class="dms-item">
                    <?= $row['is_public'] == 1 ? IfapHelper::icon("text-info zmdi-hc-2x pull-right", IfapHelper::SIGN_PUBLIC, 'public') : '' ?>
                    <?= $row->{"folder_name_$lang"} ?>

                    <?php if ($row->idpeople_owner > 0) { ?>
                        <?php $people = People::findActiveOne($row->idpeople_owner) ?>
                        <?php if ($people) { ?>
                            <br><span title="<?= WebLang::t('owner') ?>"><?= IfapHelper::SIGN_CONSULTANT ?></span>
                            <a class="btn btn-info btn-xs" href="<?= Url::to(['/consultant/view']) ?>?idpeople=<?= $people->id ?>"><?= $people->name() ?></a>
                            <?php if ($row->tree_access == 1) { ?>
                                <i class="zmdi zmdi-accounts text-warning" title="<?= WebLang::t('tree_access') ?>"></i>
                            <?php } ?>
                        <?php } else { ?>
                                <span class='text-danger'>Orphan record</a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </a>
        </div>
    <?php } ?>
<?php } else { ?>
    <p><?= WebLang::t('no_matching_record') ?></p>
<?php } ?>
