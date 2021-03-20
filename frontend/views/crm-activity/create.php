<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\CrmActivity */

$this->title = 'Create Crm Activity';
$this->params['breadcrumbs'][] = ['label' => 'Crm Activities', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="crm-activity-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
