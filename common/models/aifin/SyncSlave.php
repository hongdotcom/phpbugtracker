<?php

namespace common\models\aifin;

use Yii;

class SyncSlave extends \yii\db\ActiveRecord {

    public static $tableName = null;

    public static function getDb() {
        return Yii::$app->get('dbSlave');
    }

    public static function tableName() {
        if (!self::$tableName) {
            throw new \Exception("Invalid tableName");
        }
        return self::$tableName;
    }
    

}
