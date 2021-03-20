<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "progress".
 *
 * @property int $id
 * @property string $progress_name
 * @property int $status
 * @property int $order
 */
class Progress extends \yii\db\ActiveRecord {

    const OPEN = 1;
    const CONFIRM_BUG = 2;
    const INTERNAL_REVIEW = 6;
    const NOT_FIXED = 4;
    const CLOSE = 5;
    const CLIENT_REVIEW = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'progress';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'progress_name', 'order', 'status'], 'required'],
            [['id', 'order', 'status'], 'integer'],
            [['progress_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'progress_name' => 'Progress',
            'order' => 'Order',
            'status' => 'Status',
        ];
    }

    public function getCrmRecords($searchPhase = null, $idclient = null, $excludeCategories = null) {
        $query = $this->hasMany(CrmRecord::className(), ['idprogress' => 'id'])
                ->joinWith(['priority', 'crmActivities'])
                ->onCondition(['idprogress' => $this->id]);

        if ($searchPhase) {
            $query->andOnCondition([
                'or',
                ['like', "subject", $searchPhase],
                ['like', "internal_remark", $searchPhase],
            ]);
        }
        if ($idclient) {
            $query->andOnCondition(['idclient' => intval($idclient)]);
        }
        if ($excludeCategories) {
            $query->andOnCondition(['not in', 'idcrm_record_cat', $excludeCategories]);
        }
        $query->orderBy("priority.order ASC, crm_record.created_at DESC");
        return $query;
    }

    public function getCrmRecordCount($idclients, $iduser = NULL) {
        $query = CrmRecord::find()->select([
                    new \yii\db\Expression("count(1)")
                ])
                ->where(['idprogress' => $this->id]);

        if ($idclients) {
            $query->andWhere(['IN', 'idclient', $idclients])
                    ->andWhere(['internal_report' => 0]);
        }

        if ($iduser) {
            $query->andWhere(['idassignee' => $iduser]);
        }
        return $query->scalar();
    }

    public function name() {
        return $this->progress_name;
    }

    public static function getProgressName($currPro) {
        return Progress::find()
                        ->select('progress_name')
                        ->where(['id' => $currPro])
                        ->one();
    }

    public static function getProgressCss($progress) {
        if ($progress) {
            switch ($progress->id) {
                case Progress::OPEN:
                    $css = "grey";
                    break;
                case Progress::CONFIRM_BUG:
                    $css = "warning";
                    break;
                case Progress::INTERNAL_REVIEW:
                    $css = "primary";
                    break;
                case Progress::NOT_FIXED:
                    $css = "danger";
                    break;
                case Progress::CLIENT_REVIEW:
                    $css = "info";
                    break;
                case Progress::CLOSE:
                    $css = "success";
                    break;
                default:
                    $css = "default";
            }
        }
        return $css;
    }

}
