<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "settings".
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property int $status
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $deleted_at
 * @property int $deleted_by
 */
class Settings extends \yii\db\ActiveRecord {

    public static $key = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['key'], 'required'],
            [['status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['key'], 'string', 'max' => 50],
            [['value'], 'string', 'max' => 255],
        ];
    }

    public static function populate() {
        $settings = self::find()
                ->andWhere(['status' => 1])
                ->orderBy(['key' => SORT_ASC])
                ->asArray()
                ->all();
        self::$key = ArrayHelper::map($settings, 'key', 'value');
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

    public static function getByKey($key) {
        return self::find()->select(['value'])->where(['key' => $key])->scalar();
    }

    public static function key($key) {
        if (isset(self::$key[$key])) {
            return self::$key[$key];
        }
        return null;
    }

}
