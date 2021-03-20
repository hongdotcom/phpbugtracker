<?php

use yii\widgets\DetailView;
use common\models\WebLang;
?>

<h2 class="lead text-primary">Ticket Details</h2>

<table class="table table-striped table-condensed">
    <tr>
        <th><?= WebLang::t('progress') ?></th>
        <td><label class='label label-info'><?= $model->progress->progress_name ?></label></td>
    </tr>
    <tr>
        <th><?= WebLang::t('id') ?></th>
        <td><?= $model->id ?></td>
    </tr>
    <tr>
        <th><?= WebLang::t('subject') ?></th>
        <td><?= nl2br($model->subject) ?></td>
    </tr>
    <?php if (!empty($model->remark)) { ?>
        <tr>
            <th><?= WebLang::t('content') ?></th>
            <td><?= nl2br($model->remark) ?></td>
        </tr>
    <?php } ?>
    <tr>
        <th><?= WebLang::t('category') ?></th>
        <td><?= $model->taskCategory->task_name_eng ?></td>
    </tr>
    <tr>
        <th><?= WebLang::t('related_function') ?></th>
        <td><?= $model->crmRecordType->crm_record_type_name_eng ?></td>
    </tr>
    <?php if ($assigneeModel = $model->assigneeModel) { ?>
        <tr>
            <th><?= WebLang::t('assignee') ?></th>
            <td><?= $assigneeModel->name() ?></td>
        </tr>
    <?php } ?>
    <tr>
        <th><?= WebLang::t('priority') ?></th>
        <td><label class='label label-default'><?= $model->priority->priority_name ?></label></td>
    </tr>
    <?php if ($documents = $model->documents) { ?>
        <tr>
            <th><?= WebLang::t('attachment') ?></th>
            <td>
                <?php foreach ((array) $documents as $document) { ?>
                    <a class="btn btn-info" href="/dms/download?a=2&id=<?= $document->id ?>&token=<?= $document->token ?>" target="_new"><?= $document->orig_file_name ?></a>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>

    <?php if ($model->reminder_at) { ?>
        <tr>
            <th class='text-danger'><?= WebLang::t('deadline') ?></th>
            <td class='text-danger'><?= substr($model->reminder_at, 0, 10) ?></td>
        </tr>
    <?php } ?>
    <tr>
        <th><?= WebLang::t('created_by') ?></th>
        <td><?= $model->createdBy->name() ?> <small class='small'><?= $model->created_at ?></small></td>
    </tr>
    <?php if ($updatedBy = $model->updatedBy) { ?>
        <tr>
            <th><?= WebLang::t('updated_by') ?></th>
            <td><?= $updatedBy->name() ?> <small class='small'><?= $model->updated_at ?></small></td>
        </tr>
    <?php } ?>
</table>

<?=
$this->render('_activities', [
    'models' => $model->crmActivities,
    'idcrm_record' => $idcrm_record,
]);
?>


