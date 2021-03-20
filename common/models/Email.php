<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use common\helpers\AifinHelper;

class Email extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'email';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['subject', 'content', 'send_to'], 'required'],
            [['content', 'lasttry_error'], 'string'],
            [['idcampaign', 'idbooking', 'sent', 'smtp_id', 'on_hold', 'status', 'created_by', 'updated_by', 'deleted_by', 'approved_by', 'sending', 'idbooking'], 'integer'],
            [['sent_datetime', 'schedule_send_datetime', 'lasttry_datetime', 'created_at', 'updated_at', 'deleted_at', 'approved_at'], 'safe'],
            [['subject'], 'string', 'max' => 200],
            [['send_to', 'cc', 'bcc'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'subject' => 'Subject',
            'content' => 'Content',
            'send_to' => 'Send To',
            'idcampaign' => 'Idcampaign',
            'cc' => 'Cc',
            'bcc' => 'Bcc',
            'sending' => 'Sending',
            'sent' => 'Sent',
            'smtp_id' => 'Smtp ID',
            'sent_datetime' => 'Sent Datetime',
            'schedule_send_datetime' => 'Schedule Send Datetime',
            'lasttry_datetime' => 'Lasttry Datetime',
            'lasttry_error' => 'Lasttry Error',
            'on_hold' => 'On Hold',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
            'deleted_by' => 'Deleted By',
            'deleted_at' => 'Deleted At',
            'approved_by' => 'Approved By',
            'approved_at' => 'Approved At',
        ];
    }

    public function setDefaults() {
        $this->sending = 0;
        $this->sent = 0;
        $this->status = 0;
        $this->smtp_id = Smtp::find()->select(['id'])->where(['default' => 1])->scalar();
    }
    
    public function lastTry($msg = NULL) {
        if ($msg != NULL)
            $this->lasttry_error = $msg;
        $this->lasttry_datetime = date('Y-m-d H:i:s');
    }

    public static function findReadyEmails() {
        return self::find()
                        ->where([
                            'status' => 1,
                            'on_hold' => 0,
                            'sending' => 0,
                            'sent' => 0,
                            'lasttry_error' => NULL
                        ])
                        ->andWhere(['IS NOT', 'approved_at', NULL])
                        ->andWhere(['<=', 'schedule_send_datetime', new Expression('NOW()')])
                        ->orderBy(['schedule_send_datetime' => SORT_ASC])
                        ->all();
    }

    /**
     * Generate email record along with @common/mail/email-html template
     * Always use default=1 in smtp table
     * @param type $content
     * @param type $subContent
     * @param type $templateLang
     * @param array $params
     * @return boolean
     */
    public function generate($content, $subContent = null, $templateLang = null, $params = null) {
        if ($templateLang) {
            $lang = $templateLang;
        } else {
            $lang = Yii::$app->language;
        }

        if (!isset(Settings::$key['send_email_delay_mins'])) {
            $delayMins = Settings::getByKey('send_email_delay_mins');
        } else {
            $delayMins = Settings::$key['send_email_delay_mins'];
        }

        $smtp = Smtp::find()->where(['default' => 1])->one();

        if (!$smtp) {
            return FALSE;
        }

        $this->smtp_id = $smtp->id;

        $this->on_hold = 0;
        $this->status = 0;
        $this->schedule_send_datetime = AifinHelper::timestamp($delayMins);

//        $buttonUrl = Yii::$app->urlManagerFrontend->createAbsoluteUrl(['/']);
        $buttonUrl = "https://bugtracker.aifin.asia/";

        if (isset($params['buttonUrl'])) {
            $buttonUrl = $params['buttonUrl'];
        } else {
            $buttonUrl = "https://bugtracker.aifin.asia/";
        }
        if (isset($params['buttonText'])) {
            $buttonText = $params['buttonText'];
        } else {
            $buttonText = WebLang::t('goto_your_account', $lang);
        }
        /*
         * Process template variables
         */
        $this->content = Yii::$app->controller->renderPartial("@common/mail/email-html", [
            'coName' => Settings::getByKey("co_name_$lang"),
            'slogan' => Settings::getByKey("slogan_$lang"),
            'logoUrl' => $buttonUrl . "images/portal_logo/" . Settings::getByKey("logo_file"),
            'buttonUrl' => $buttonUrl,
            'buttonText' => $buttonText,
            'csName' => WebLang::t('customer_service_team', $lang),
            'csEmail' => Settings::getByKey("cs_email"),
            'title' => trim($this->subject),
            'content' => trim($content),
            'subContent' => trim($subContent),
        ]);

        return TRUE;
    }

}
