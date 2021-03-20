<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "smtp_log".
 *
 * @property int $id
 * @property string $type
 * @property string $msg
 * @property string $created_at
 */
class SmtpLog extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'smtp_log';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['type', 'msg', 'created_at'], 'required'],
            [['msg'], 'string'],
            [['created_at'], 'safe'],
            [['type'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'msg' => 'Msg',
            'created_at' => 'Created At',
        ];
    }

    public static function add($type = "info", $msg) {
        if (!$msg) {
            return FALSE;
        }

        $smtpLog = new SmtpLog();

        if (!is_array($msg)) {
            $smtpLog->type = $type;
            $smtpLog->msg = $msg;
            $smtpLog->created_at = date(\common\helpers\AifinHelper::DATETIMEFORMAT);

            if (!$smtpLog->save()) {
                return FALSE;
            }
        } else {
            $smtpLog->type = $type;
            $smtpLog->created_at = date(\common\helpers\AifinHelper::DATETIMEFORMAT);

            foreach ($msg as $item) {
                $smtpLog->msg .= $item;
            }
            if (!$smtpLog->save()) {
                return FALSE;
            }
        }
        return TRUE;
    }

}
