<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\Settings;
use common\models\Smtp;
use common\models\WebLang;
use common\models\EmailAttachment;
use common\helpers\AifinHelper;
use common\models\SmtpLog;
use common\models\Email;

class EmailController extends Controller {

    public $lang;
    public $smtplog = null;

    public function init() {
        parent::init();

//        Yii::$app->user->setIdentity(User::findOne(['id' => 1]));
        $this->lang = "eng";
        Yii::$app->language = $this->lang;
    }

    public function actionIndex() {
        self::dd("Service is ok");
    }

    public static function dd($str) {
        echo date(AifinHelper::DATETIMEFORMAT) . " " . $str . PHP_EOL;
    }

    public function sendRichEmail($email) {

        $message = Yii::$app->mailer->compose([
            'html' => 'email-html',
//            'text' => 'email-text'
                ], [
            'coName' => Settings::getBykey("co_name_{$this->lang}"),
            'slogan' => Settings::getBykey("slogan_{$this->lang}"),
            'csName' => WebLang::t('cs'),
            'csEmail' => Settings::getBykey('cs_email'),
            'title' => $email->subject,
            'content' => $email->content,
        ]);

        $message->setFrom([Settings::getByKey('CS_EMAIL') => Settings::getByKey('co_name_eng')]);
        $message->setTo($email->send_to)
                ->setSubject($email->subject)
                ->send();
    }

    public function process($email) {

        if (!$email) {
            throw new \Exception('Invalid email model');
        }

        $email->sending = 1; // to prevent other process instance send this email at the same time
        $email->update();

        if ($email->status != 0)
            return false;

        // send the message
        $message = Yii::$app->mailer->compose();

        $contentHeader = "<html><body>";
        $contentFooter = "</body></html>";

        if (!empty($email->send_to)) {
            $message->setFrom([Settings::getByKey('CS_EMAIL') => Settings::getByKey('co_name_eng')]);
            $message->setTo($email->send_to)
                    ->setSubject($email->subject)
                    ->setHtmlBody($contentHeader . $email->content . $contentFooter)
                    ->send();
        }

        $email->status = 1;
        $email->sent = 1;
        $email->sent_datetime = date(\common\helpers\AifinHelper::DATETIMEFORMAT);
        $email->update();
    }

    public function actionProcessTickets() {
        $crmRecords = \common\models\CrmRecord::find()
                ->where(['<>', 'idprogress', 5])
                ->all();

        if (!$crmRecords) {
            return TRUE; // no pending
        }

        $content = NULL;

        foreach ($crmRecords as $crmRecord) {
            $assignee = $crmRecord->assigneeModel;
            
            if(!$assignee) { 
                continue;
            }
            
            $sendTo = $assignee->email;
            $subject = WebLang::t('Ticket') . " " . WebLang::t('reminder') . " - " . $crmRecord->crmRecordType->crm_record_type_name_eng;

            if (!empty($sendTo)) {
                $email = new Email();
                $email->subject = $subject;
                $email->send_to = $sendTo;
                $email->generate($crmRecord->remark);

                if (!$email->save()) {
                    throw new \Exception(var_dump($email->getErrors()));
                }
            } else {
                Yii::error("assignee does not have email address");
            }
        }
    }

    public function actionProcess() {
        $unsentIdList = [];

        ini_set('max_execution_time', 5000);

        $threshold = 50; // max no. of email sending

        $countSentEmail = 0;
        $sentEmailId = array();

        $log = null;

        $log .= "Start processing email queue, threshold=$threshold";

        while ($threshold > 0) {
            $threshold--;

            $time_start = microtime(true);

            $email = \common\models\Email::find()
                    ->where(['status' => 0])
                    ->andWhere(['<>', 'sending', 1])
                    ->andWhere(['or',
                        ['<=', 'schedule_send_datetime', date(\common\helpers\AifinHelper::DATETIMEFORMAT)],
                        ['schedule_send_datetime' => null]
                    ])
                    ->orderBy(['id' => 'ASC'])
                    ->one();

            if ($email == NULL) {
                self::dd($msg = "No more pending email found");
                SmtpLog::add("info", $msg);
                break;
            }

            self::dd('Sending email id: ' . $email->id);

            // Mark current email is sending to avoid multi-thread processEmailQueue running
            $email->sending = 1;
            $email->lasttry_datetime = date(\common\helpers\AifinHelper::DATETIMEFORMAT);
            $email->save();

            //find default smtp
            $smtp = Smtp::findOne(['default' => 1]);

            if (!$smtp) {
                $msg = "smtp record not found";
                SmtpLog::add("error", $msg);
                throw new \Exception($msg);
            }

            //config smtp
            Yii::$app->set('mailer', [
                'class' => 'yii\swiftmailer\Mailer',
                'useFileTransport' => false,
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
                    'host' => $smtp->host,
                    'username' => $smtp->username,
                    'password' => $smtp->password,
                    'port' => $smtp->port,
                    'encryption' => $smtp->encryption,
                ],
            ]);

            $sendToArray = explode(',', $email['send_to']);

            $mailer = Yii::$app->mailer;

            $message = Yii::$app->mailer->compose();
            $message->setReturnPath(Settings::getByKey('cs_email'));
            $message->setFrom([Settings::getByKey('cs_email') => Settings::getByKey('co_name_eng')]);
            $contentHeader = "<html><body>";
            $contentFooter = "</body></html>";

            if (!empty($email->send_to)) {
                $message->setReturnPath(Settings::getByKey('cs_email'));
                $message->setFrom([Settings::getByKey('cs_email') => Settings::getByKey('co_name_eng')]);
                $message->setTo($email->send_to)
                        ->setSubject($email->subject)
                        ->setHtmlBody($contentHeader . $email->content . $contentFooter);
            } else {
                self::dd("Empty send to, skipped");
            }

            //check if this email has attachment
            $emailAttachments = EmailAttachment::getEmailAttachments($email->id);

            if (!empty($emailAttachments)) {
                foreach ($emailAttachments as $attachment) {
                    self::dd("Attached attachment id={$attachment->id}");

                    /*
                     * SHOULD LIMIT TO certain folder or not?
                     */
//                    $pathToFile = Yii::$app->params['dmsPath'] . $attachment['filename'];
                    $pathToFile = $attachment['filename'];

                    if (file_exists($pathToFile)) {
                        $message->attach($pathToFile);
                    } else {
                        self::dd("Attachment NOT FOUND at $pathToFile");
                    }
                }
            }

            $ccArray = explode(',', $email['cc']);
            if (!empty($email['cc'])) {
                $message->setCc($ccArray);
            }

            $bccArray = [];
            if ($email->bcc != NULL)
                $bccArray = explode(',', $email['bcc']);

            if (!empty($bccArray)) {
                $message->setBcc($bccArray);
            }

            self::dd('Started swiftmailer session');

            try {
                if (Yii::$app->mailer->getTransport()->isStarted()) {
                    Yii::$app->mailer->getTransport()->stop();
                }
                Yii::$app->mailer->getTransport()->start();

                if ($mailer->send($message)) {
                    $email->status = 1; // success
                    $email->sending = 0;
                    $email->sent = 1;
                    $email->sent_datetime = date(\common\helpers\AifinHelper::DATETIMEFORMAT);
                    $email->save();

                    $countSentEmail++;
                    $sentEmailId[] = $email->id;
                    self::dd("Success sending email id " . $email->id);
                }
            } catch (\Exception $e) {
                $unsentIdList[] = $email->id;
                $email->status = 2; // failed with error, NO retry
                $email->sending = 0;
                $email->lasttry_error = var_export($e->getMessage(), true);
                $email->save();

                self::dd("ERROR sending email id " . $email->id);
                self::dd("host {$smtp->host} port {$smtp->port}, encryption {$smtp->encryption}");
                var_dump($e->getMessage());
                $msg .= PHP_EOL . "Marked as no-retry (status 2)";
                $msg .= PHP_EOL . $e->getMessage();

                SmtpLog::add("error", $msg);
                self::dd($msg);
            }

            self::dd('- End of email id ' . $email->id);
        }

        $msg = "Total email sent: " . $countSentEmail . " , ";
        $msg .= "id: " . implode(',', $sentEmailId);
        SmtpLog::add("info", $msg);

        $time_end = microtime(true);
        self::dd('End of processing.  Time elapsed is ' . round($time_end - $time_start, 2) . ' seconds');
    }

}
