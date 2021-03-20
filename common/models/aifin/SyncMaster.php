<?php

namespace common\models\aifin;

use Yii;

class SyncMaster extends \yii\db\ActiveRecord {

    public static $tableName = null;

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    public static function tableName() {
        if (!self::$tableName) {
            throw new \Exception("Invalid tableName");
        }
        return self::$tableName;
    }

}
