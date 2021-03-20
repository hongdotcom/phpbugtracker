<?php

use yii\grid\GridView;
use common\models\User;
use common\models\CrmRecord;
use common\models\TaskCategory;
use common\models\ClientSite;
use common\models\Priority;
use common\models\Progress;
?>
<div class="col-sm-12">
    <div class="panel panel-warning m-t-10">
        <div class="panel-heading">
            <h4 class="panel-title"><?= $title ?></h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <?=
                GridView::widget([
                    'dataProvider' => $dataProviderOpenRecords,
                    'tableOptions' => [
                        'class' => 'table table-striped table-condensed',
                    ],
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'columns' => [
                        'id',
                        ['label' => 'Priority',
                            'attribute' => 'idpriority',
                            'format' => 'raw',
                            'value' => function($model) {
                                $priority = Priority::find()
                                        ->where(['id' => $model->idpriority])
                                        ->one();
                                if ($model->idpriority <> 4) {
                                    switch ($model->idpriority) {
                                        case 1:
                                        case 2:
                                            $cssClass = 'danger';
                                            break;
                                        case 3:
                                            $cssClass = 'warning';
                                            break;
                                        case 4:
                                            $cssClass = 'primary';
                                            break;
                                        case 5:
                                            $cssClass = 'success';
                                            break;
                                        case 6:
                                            $cssClass = 'default';
                                            break;
                                    }
                                    return "<label class='label label-$cssClass'>{$priority->priority_name}</label>";
                                } else {
                                    return '';
                                }
                            }
                        ],
                        ['label' => 'Path',
                            'attribute' => 'idassignee',
                            'format' => 'raw',
                            'value' => function($model) {
                                if ($model->created_by == Yii::$app->user->identity->id) {
                                    $from = "You";
                                } else {
                                    $from = $model->createdBy->name();
                                }
                                $assignee = CrmRecord::getAssignee($model->idassignee);
                                if ($assignee) {
                                    if ($model->idassignee == Yii::$app->user->identity->id) {
                                        $to = "You";
                                    } else {
                                        $to = $assignee->name();
                                    }
                                    return "<label class='label label-primary'>$from to $to";
                                } else {
                                    return null;
                                }
                            }
                        ],
                        ['label' => 'Last Activity',
                            'format' => 'raw',
                            'value' => function($model) {
                                if ($model) {
                                    if ($model->lastCrmActivity) {
                                        
                                    }
                                    if ($model->lastCrmActivity) {
                                        $username = User::getUsername($model->lastCrmActivity->created_by);
                                        if ($model->lastCrmActivity->created_by == Yii::$app->user->identity->id) {
                                            $username = "<label class='label label-primary'>You</label>";
                                        }
                                        return "" .
                                                Yii::$app->formatter->asDate($model->lastCrmActivity->created_at, 'yyyy-MM-dd HH:mm') .
//                                                            " by " .
                                                "<label class='label m-l-10 label-primary'>$username</label>";
                                    }
                                }
                                return null;
                            }
                        ],
                        ['label' => 'Progress',
                            'attribute' => 'idprogress',
                            'format' => 'raw',
                            'value' => function($model) {
                                $progress = Progress::find()
                                        ->where(['id' => $model->idprogress])
                                        ->one();
                                if ($progress) {
                                    $css = Progress::getProgressCss($progress);
                                    return "<label class='label label-$css'>" . $progress->progress_name . "</label>";
                                } else {
                                    return null;
                                }
                            }
                        ],
                        ['label' => 'Subejct',
                            'attribute' => 'subject',
                            'format' => 'raw',
                            'value' => function($model) {
                                if ($model) {
                                    $out = strlen($model->subject) > 80 ? substr($model->subject, 0, 80) . "..." : $model->subject;
                                } else {
                                    $out = null;
                                }
                                $return = "<a href='#' class='text-primary' onclick='toggleActivityModal(\"#activity-modal\", {$model->id})' .
                        >$out</a>";
                                return $return;
                            }
                        ],
                        ['label' => 'Category',
                            'attribute' => 'idcrm_record_cat',
                            'value' => function($model) {
                                $TaskCategory = TaskCategory::find()->
                                                where(['id' => $model->idcrm_record_cat])->one();
                                return $TaskCategory->task_name_eng;
                            }
                        ],
                        ['label' => 'Client',
                            'attribute' => 'idclient',
                            'value' => function($model) {
                                $client = ClientSite::find()
                                        ->where(['id' => $model->idclient])
                                        ->one();
                                if ($client) {
                                    return $client->client_name;
                                } else {
                                    return null;
                                }
                            }
                        ],
                        ['label' => 'Created at',
                            'value' => function($model) {
                                $created_at = Yii::$app->formatter->asDate($model->created_at, 'yyyy-MM-dd HH:mm');
                                if ($created_at) {
                                    return $created_at;
                                } else {
                                    return null;
                                }
                            }
                        ],
//                                    ['label' => 'Last Update',
//                                        'format' => 'raw',
//                                        'value' => function($model) {
//                                            if ($model) {
//                                                if ($model->lastCrmActivity) {
//                                                    $username = User::getUsername($model->lastCrmActivity->created_by);
//                                                    if ($model->lastCrmActivity->created_by == Yii::$app->user->identity->id) {
//                                                        return "<label class='label label-primary'>You</label>";
//                                                    } else {
//                                                        return $username;
//                                                    }
//                                                }
//                                            }
//                                            return null;
//                                        }
//                                    ],
//                                'lastCrmActivity.updated_at',
//                                    ['label' => 'Content',
//                                        'attribute' => 'remark',
//                                        'format' => 'html',
//                                        'value' => function($model) {
//                                            $out = strlen($model->remark) > 50 ? substr($model->remark, 0, 60) . "..." : $model->remark;
//                                            return $out;
//                                        }
//                                    ],
//                                    ['label' => 'Created By',
//                                        'attribute' => 'created_by',
//                                        'value' => function($model) {
//                                            return $model->createdBy->username;
//                                        }
//                                    ],
                        ['class' => 'yii\grid\ActionColumn',
                            'template' => '{update}',
                            'visible' => (User::getIsAdmin($user)),
                        ],
                        ['class' => 'yii\grid\ActionColumn', 'template' => '{delete}'],
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
</div>
