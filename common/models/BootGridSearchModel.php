<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Json;

class BootGridSearchModel extends Model {

    /** @var ActiveQuery $query */
    public $query;
    public $searchPhrase;
    public $current;
    public $rowCount;
    public $sort;
    public $offset;
    public $model;
    public $searchColumns;
    public $integerColumns = [];

    public function __construct(ActiveRecord $model, array $searchColumns, array $config = []) {
        $this->model = $model;
        $this->searchColumns = $searchColumns;

        parent::__construct($config);
    }

    public function init() {
        $this->searchPhrase = Yii::$app->request->post('searchPhrase', NULL);
        $this->current = Yii::$app->request->post('current');
        $this->rowCount = Yii::$app->request->post('rowCount');
        $this->sort = Yii::$app->request->post('sort', NULL);

        if ($this->searchPhrase != NULL)
            $this->searchPhrase = trim($this->searchPhrase);

        if ($this->current <= 0)
            $this->current = 1;

        $this->rowCount = (int) $this->rowCount;

        if ($this->rowCount <= 0) {
            if ($this->rowCount <> -1) {
                $this->rowCount = 10;
            }
        }
        $this->offset = ($this->current - 1) * $this->rowCount;

        $this->query = new Query();
    }

    public function search() {
        $this->query->from($this->model->tableName());

        if ($this->model->tableName() == "plan") {
            $this->searchByKeyword()
                    ->sort()
                    ->paginate();
        } else {
            $this->defaultWhere()
                    ->searchByKeyword()
                    ->sort()
                    ->paginate();
        }
        $this->query->selectOption = 'SQL_CALC_FOUND_ROWS';

        $results = $this->query->createCommand()->queryAll();

        if (!empty($this->integerColumns)) {
            foreach ($results as &$result) {
                foreach ($this->integerColumns as $integerColumn) {
                    if (isset($result[$integerColumn]))
                        $result[$integerColumn] = (integer) $result[$integerColumn];
                }
            }
        }

        $total = Yii::$app->db->createCommand("SELECT FOUND_ROWS()")->queryScalar();
        
        // append checkpoint
        foreach ($results as &$result) {
            if (isset($result['idplan'])) {
                $plan = Plan::findActiveOne($result['idplan']);
                if ($plan) {
                    $checkpoint = $plan->checkpoint;
                    $result['checkpoint'] = \common\helpers\IfapHelper::planCheckpointHtml($checkpoint); // gen the label
                } else {
                    $result['checkpoint'] = null;
                }
            }
        }

        return Json::encode([
                    'total' => $total,
                    'rows' => $results,
                    'current' => intval($this->current),
                    'rowCount' => intval($this->rowCount),
                    'success' => TRUE
        ]);
    }

    public function searchEmpty() {
        return Json::encode([
                    'total' => 0,
                    'rows' => [],
                    'current' => intval($this->current),
                    'rowCount' => intval($this->rowCount),
                    'success' => FALSE
        ]);
    }

    private function defaultWhere() {
        $this->query->andWhere(['<>', $this->model->tableName() . '.status', 0]);
        return $this;
    }

    private function searchByKeyword() {
        if ($this->searchPhrase != '' && $this->searchPhrase != NULL) {
            $searchPhraseArray = explode(',', $this->searchPhrase);

            foreach ($searchPhraseArray as $keyword) {
                $keyword = strtolower($keyword);

                $whereArray = ['OR'];
                foreach ($this->searchColumns as $searchColumn) {
                    $whereArray[] = ['like', new Expression($searchColumn), $keyword];
                }
                $this->query->andFilterWhere($whereArray);
            }
        }
        return $this;
    }

    private function sort() {
        if ($this->sort != NULL) {
            $this->sort = filter_var_array($_POST['sort'], FILTER_SANITIZE_STRING);

            foreach ((array) $this->sort as $key => $value) {
                $this->query->addOrderBy($key . ' ' . $value);
            }
        }
        return $this;
    }

    private function paginate() {
        $this->query->limit($this->rowCount)
                ->offset($this->offset);
        return $this;
    }

    public function setIntegerColumns(array $integerColumns) {
        $this->integerColumns = $integerColumns;
    }

}
