<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\CrmRecord */

$this->title = 'Update ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['update', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="crm-record-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
