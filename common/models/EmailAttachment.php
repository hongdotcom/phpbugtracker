<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "email_attachment".
 *
 * @property integer $id
 * @property integer $email_id
 * @property string $filename
 */
class EmailAttachment extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'email_attachment';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['email_id', 'filename'], 'required'],
            [['email_id'], 'integer'],
            [['filename'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'email_id' => 'Email ID',
            'filename' => 'Filename',
        ];
    }

    public static function getEmailAttachments($emailId) {
        return self::find()->where([
                    'email_id' => $emailId,
                    'status' => 1,
                ])->all();
    }

}
