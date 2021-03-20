<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "client".
 *
 * @property int $id
 * @property string $priority_name
 * @property int $status
 * @property int $order
 */
class Priority extends \yii\db\ActiveRecord {

    const TOP_URGENT = 1;
    const URGENT = 2;
    const HIGH = 3;
    const NORMAL = 4;
    const LOW = 5;
    const NICE_TO_HAVE = 6;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'priority';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'priority_name', 'order', 'status'], 'required'],
            [['id', 'order', 'status'], 'integer'],
            [['priority_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'priority_name' => 'Priority',
            'order' => 'Order',
            'status' => 'Status',
        ];
    }

    public function getCrmRecords() {
        return $this->hasMany(CrmRecord::className(), ['idpriority' => 'id']);
    }

    public function name() {
        return $this->priority_name;
    }

    public function nameBadge() {
        switch ($this->id) {
            case Priority::TOP_URGENT:
            case Priority::URGENT:
                $color = "danger";
                break;
            case Priority::HIGH:
                $color = "warning";
                break;
            case Priority::NORMAL:
                $color = "info";
                break;
            case Priority::LOW:
            case Priority::NICE_TO_HAVE:
                $color = "default";
                break;
        }
        return "<span class='badge badge-$color'>" . $this->priority_name . "</span>";
    }

    public function cssColor() {
        switch ($this->id) {
            case Priority::TOP_URGENT:
            case Priority::URGENT:
                $color = "danger";
                break;
            case Priority::HIGH:
                $color = "warning";
                break;
            case Priority::NORMAL:
                $color = "default";
                break;
            case Priority::LOW:
            case Priority::NICE_TO_HAVE:
                $color = "info";
                break;
            default:
                $color = "primary";
                break;
        }
        return $color;
    }

}
