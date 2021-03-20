<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "smtp".
 *
 * @property integer $id
 * @property string $host
 * @property string $username
 * @property string $password
 * @property string $from
 */
class Smtp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'smtp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['host', 'username', 'password', 'from'], 'required'],
            [['host', 'username', 'password', 'from'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'host' => 'Host',
            'username' => 'Username',
            'password' => 'Password',
            'from' => 'From',
        ];
    }
}
