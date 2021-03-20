<?php

namespace common\models;

use common\behaviors\AuditTrailBehavior;
use Yii;
use common\behaviors\BlameableBehavior;
use common\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * Class BaseAR
 * @package common\models
 *
 * @property integer $id
 * @property integer $status
 * @property integer $created_by
 * @property string $created_at
 * @property integer $updated_by
 * @property string $updated_at
 * @property integer $deleted_at
 * @property integer $deleted_by
 *
 * Relationship
 * @property People $createdBy
 * @property People $updatedBy
 * @property People $deletedBy
 * @property AuditTrail $auditTrails
 * @property AuditTrail[] $relatedAuditTrails
 */
class BaseAR extends ActiveRecord {

    public function behaviors() {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::className(),
                'typecastAfterValidate' => true, // this is Yii2 default
                'typecastBeforeSave' => true, // yii2 default false
                'typecastAfterFind' => true, // yii2 default false
            ],
//            [
//                'class' => TimestampBehavior::className(),
//                'value' => date(\common\helpers\IfapHelper::DATETIMEFORMAT)
//            ],
        ];
    }

    /**
     * @return bool
     */
    public function softDelete() {
        $this->status = 0;
        $this->deleted_at = date(\common\helpers\IfapHelper::DATETIMEFORMAT);

        if (Yii::$app->user->isGuest)
            $this->deleted_by = 0;
        else
            $this->deleted_by = Yii::$app->user->identity->id;

        return $this->save(FALSE);
    }

    /**
     * @param string $condition
     * @param array $params
     * @return int
     */
    public static function softDeleteAll($condition = '', $params = []) {
        if (Yii::$app->user->isGuest)
            $deletedBy = 0;
        else
            $deletedBy = Yii::$app->user->identity->id;

        return static::updateAll([
                    'status' => 0,
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'deleted_by' => $deletedBy,
                        ], $condition, $params);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy() {
        return $this->hasOne(People::className(), ['id' => 'idpeople'])
                        ->from(People::tableName() . ' createdBy')
                        ->viaTable(UserPeople::tableName(), ['iduser' => 'created_by'], function ($query) {
                            return $query->from(UserPeople::tableName() . ' createdByUser')
                                    ->onCondition(['createdByUser.primary_account' => 1]);
                        });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy() {
        return $this->hasOne(People::className(), ['id' => 'idpeople'])
                        ->from(People::tableName() . ' updatedBy')
                        ->viaTable(UserPeople::tableName(), ['iduser' => 'updated_by'], function ($query) {
                            return $query->from(UserPeople::tableName() . ' updatedByUser')
                                    ->onCondition(['updatedByUser.primary_account' => 1]);
                        });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeletedBy() {
        return $this->hasOne(People::className(), ['id' => 'idpeople'])
                        ->from(People::tableName() . ' deletedBy')
                        ->viaTable(UserPeople::tableName(), ['iduser' => 'deleted_by'], function ($query) {
                            return $query->from(UserPeople::tableName() . ' deletedByUser')
                                    ->onCondition(['deletedByUser.primary_account' => 1]);
                        });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuditTrails() {
        $alias = static::tableName() . 'Audit_' . \common\helpers\IfapHelper::genRandomKey();

        return $this->hasMany(AuditTrail::className(), ['model_id' => 'id'])
                        ->from(AuditTrail::tableName() . ' ' . $alias)
                        ->onCondition([$alias . '.table' => static::tableName()])
                        ->andOnCondition(['<>', $alias . ".action", 'CREATE'])
                        ->distinct()
                        ->asArray();
    }

    /**
     * Support multi-level relations just like Yii2 defined, i.e 'insureds.people'
     * Support HasOne and HasMany relations
     * Loop through every relation and get the auditTrails relation
     * Push to an array and reorder it
     * @return array|AuditTrail[]
     */
    public function getRelatedAuditTrails() {
        $logs = null;

        $query = static::find()
                ->joinWith([
                    'auditTrails' => function ($query) {
                        return $query->indexBy('id');
                    },
//                    'auditTrails',
                    'auditTrails.createdBy'
                ])
                ->where([static::tableName() . '.id' => $this->id]);

        if (!empty($this->relatedAudits)) {
            foreach ($this->relatedAudits as $relatedAudit) {

                $relationships = explode('.', $relatedAudit);

                foreach ($relationships as $index => $relationship) {
                    $temp = array_slice($relationships, 0, $index + 1);
                    $_relationship = implode('.', $temp);
                    $query->joinWith([
                        $_relationship . '.auditTrails' => function ($query) {
                            return $query->indexBy('id');
                        },
                        $_relationship . '.auditTrails.createdBy'
                    ]);
                }
            }
        }

        /** @var BaseAR $result */
        $result = null;

        try {
            $result = $query
                    ->distinct()// 2018-10-8 this is critical to minimize memory usage
                    ->one();
        } catch (\Exception $e) {
//            var_dump($query->createCommand()->rawSql);
            dd($e);
        }
        $logs = $result->auditTrails;

        if (!empty($this->relatedAudits)) {
            foreach ($this->relatedAudits as $relatedAudit) {

                $_relationship = explode('.', $relatedAudit);

                if ($result) {
                    $this->recursiveGetAuditTrails($result->{$_relationship[0]}, $_relationship, 0, $logs);
                }
            }
        }

        ArrayHelper::multisort($logs, ['id'], [SORT_DESC]);

        return $logs;
    }

    private function recursiveGetAuditTrails($results, $relationships, $index, &$logs) {
        if (empty($results) || !isset($relationships[$index]))
            return;

        if (is_array($results)) {
            foreach ($results as $result) {
                $logs = ArrayHelper::merge($logs, $result->auditTrails);

                if (isset($relationships[$index + 1]) && $result->{$relationships[$index + 1]})
                    $this->recursiveGetAuditTrails($result->{$relationships[$index + 1]}, $relationships, $index + 1, $logs);
            }
        } else {
            $logs = ArrayHelper::merge($logs, $results->auditTrails);

            if (isset($relationships[$index + 1]) && $results->{$relationships[$index + 1]})
                $this->recursiveGetAuditTrails($results->{$relationships[$index + 1]}, $relationships, $index + 1, $logs);
        }
    }

    /**
     * @param $primaryKey
     * @return static
     */
    public static function findNotDeletedOne($primaryKey) {
        return static::find()->where(['id' => $primaryKey])->andWhere(['<>', 'status', 0])->one();
    }

    public static function findNotDeletedAsArrayOne($primaryKey) {
        return static::find()->where(['id' => $primaryKey])->andWhere(['<>', 'status', 0])->asArray()->one();
    }

    /**
     * @param $primaryKey
     * @return static
     */
    public static function findActiveOne($primaryKey) {
        return static::findOne(['id' => $primaryKey, 'status' => 1]);
    }

    public static function findActiveOneAsArray($primaryKey) {
        return static::find()
                        ->where(['id' => $primaryKey, 'status' => 1])->asArray()->one();
    }

    /**
     * @param array $filter
     * @param int $limit
     * @return array|ActiveRecord[]
     */
    public static function findActiveAll($filter = null, $limit = null) {
        if ($filter) {
            $query = static::find()
                    ->where([$filter, 'status' => 1]);
        } else {
            $query = static::find()
                    ->where(['status' => 1]);
        }

        if ($limit) {
            $query->limit($limit);
        }
        return $query->all();
    }

    public static function checkExists($primaryKey) {
        return static::find()->where(['id' => $primaryKey])->andWhere(['<>', 'status', 0])->exists();
    }

    public function findDiff($columns) {
        $dbData = static::find()
                ->select($columns)
                ->where([
                    'status' => 1,
                    'id' => $this->id,
                ])
                ->asArray()
                ->one();

        if ($dbData == NULL)
            return [];

        $diff = [];

        foreach ($dbData as $k => $v) {
            // ignore some stupid null/empty/0 old = new values
            $varType = gettype($v); // DB column data type
            $writeAuditLog = TRUE;

            if (isset($this->$k)) {
                if ((string) $this->$k == (string) $v) {
                    $writeAuditLog = FALSE;
                }

                if (($this->$k == null || $this->$k == "" || empty($this->$k) || $this->$k == "0000-00-00") &&
                        ($v == null || $v == "" || empty($v) || $v == "0000-00-00")
                ) {
                    $writeAuditLog = FALSE;
                }

                if ($writeAuditLog) {
                    $diff[] = array('title' => $k,
                        'old' => $v,
                        'new' => $this->$k);
                }
            }
        }
        return $diff;
    }

    /**
     * @param $primaryKey
     * @return array|ActiveRecord[]
     */
    public static function findActiveAllAsArray($primaryKey = null, $limit = null) {
        if ($primaryKey) {
            $query = static::find()
                    ->where(['id' => $primaryKey, 'status' => 1]);
        } else {
            $query = static::find()
                    ->where(['status' => 1]);
        }

        if ($limit) {
            $query->limit($limit);
        }
        return $query->asArray()->all();
    }

}
