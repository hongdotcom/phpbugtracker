<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\CrmActivity */

$this->title = 'Update Crm Activity: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Crm Activities', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="crm-activity-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
