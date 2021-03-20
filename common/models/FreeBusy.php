<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "booking".
 *
 * @property integer $id
 * @property string $purpose
 * @property string $timeslot
 * @property integer $idproduct
 * @property integer $no_of_clients
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 */
class FreeBusy extends \yii\db\ActiveRecord {

    const BUSY = 0;
    const FREE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'freebusy';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
//            [['event_date'], 'date', 'format' => 'yyyy-mm-dd'],
            [['event_date', 'idtimeslot', 'status'], 'required'],
            [['status', 'created_by', 'updated_by', 'deleted_by', 'idtimeslot'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['remark'], 'string', 'max' => 255],
        ];
    }

    public function getTimeSlot() {
        return $this->hasOne(TimeSlot::className(), ['id' => 'idtimeslot'])
                        ->onCondition([TimeSlot::tableName() . '.status' => 1]);
    }
    public function getCreatedBy() {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

}
