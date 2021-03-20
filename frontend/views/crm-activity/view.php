<?php

use yii\helpers\Html;
use yii\widgets\DetailView;


/* @var $this yii\web\View */
/* @var $model common\models\CrmActivity */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Crm Activities', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="crm-activity-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
          <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    
          <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'idcrm_record',
            'action_date',
            'internal_remark:ntext',
            'agent_remark:ntext',
            //(Settings::$key['client_service'] == 1 ? 'client_remark:ntext' : ''),
            'reminder_date',
           // 'email_notification:email',
            'created_at',
            'updated_at',
            'deleted_at',
            'created_by',
            'updated_by',
            'deleted_by',
            'status',
            'progress',
            'request_date',
            'request_to',
            'received_date',
            'received_from',
            'delivery_date',
            'delivery_to',
            'provider_remark:ntext',
        ],
    ]) ?>

</div>
