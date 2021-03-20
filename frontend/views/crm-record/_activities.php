<?php

use common\models\User;
use common\helpers\AifinHelper;
use common\models\WebLang;

if (!$models) {
    return;
}
?>

<h4 class="text-primary">History</h4>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= WebLang::t('created_at') ?></th>
                <th><?= WebLang::t('content') ?></th>
            </tr>
        </thead>
        <?php foreach ((array) $models as $item) { ?>
            <tr>
                <td><span class="strong"><?= User::getUsername($item->created_by) ?></span> <small class="small"><?= $item->created_at ?></small></td>
                <td>
                    <?= nl2br($item->internal_remark) ?>
                    <div class='clearfix'></div>
                    
                    <?php $documents = $item->documents ?>
                    <?php foreach ((array) $documents as $document) { ?>
                        <a class="btn btn-info btn-xs" href="/dms/download?a=2&id=<?= $document->id ?>&token=<?= $document->token ?>" target="_new">
                            <i class='fa fa-file'></i> <?= $document->orig_file_name ?>
                        </a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>