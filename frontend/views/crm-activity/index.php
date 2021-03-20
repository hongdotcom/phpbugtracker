<?php

use yii\helpers\Html;
use yii\grid\GridView;

//use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Crm Activities';
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="crm-activity-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('New Work Log', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'idcrm_record',
            'action_date',
            'internal_remark:ntext',
            'agent_remark:ntext',
            // 'client_remark:ntext',
            // 'reminder_date',
            // 'email_notification:email',
            // 'created_at',
            // 'updated_at',
            // 'deleted_at',
            // 'created_by',
            // 'updated_by',
            // 'deleted_by',
            // 'status',
            // 'progress',
            // 'request_date',
            // 'request_to',
            // 'received_date',
            // 'received_from',
            // 'delivery_date',
            // 'delivery_to',
            // 'provider_remark:ntext',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => $actionColumnTemplate,
            ],
        ],
    ]); ?>
</div>
