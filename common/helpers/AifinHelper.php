<?php

namespace common\helpers;

use common\models\WebLang;
use Yii;
use kartik\mpdf\Pdf;
use common\models\Settings;
use common\models\UserClientRelationship;
use common\models\UserRoleRelationship;

//use yii\helpers\Url; <-- dont use this as it brings weird "already in use" error for now

class AifinHelper extends \yii\base\Model {

// FRONTEND
    const ICO_CRM = "<i class='zmdi zmdi-account-box-phone zmdi-hc text-warning'></i>";
    const ICO4X_CRM = "<i class='zmdi zmdi-account-box-phone zmdi-hc-4x text-warning'></i>";
    const ICO2X_CS = "<span><i class='zmdi zmdi-phone-msg zmdi-hc-2x text-warning' title='CS'></i></span>";
    const ICO2X_NB = "<i class='zmdi zmdi-alarm-plus zmdi-hc-2x text-warning' title='NB'></i>";
    const ICO4X_NB = "<i class='zmdi zmdi-alarm-plus zmdi-hc-4x c-white'></i>";
    const ICO_DOCUMENT = "<i class='zmdi zmdi-file zmdi-hc-2x text-warning'></i>";
    const ICO_FOLDER = "<i class='zmdi zmdi-folder zmdi-hc-2x pointer' style='margin-top: -5px'></i>";
    const ICO_GOBACK = "<button class='btn bgm-green btn-icon waves-effect waves-circle waves-float'><i class='zmdi zmdi-arrow-left'></i></button>";
    const ICO_WRITING = "<i class='zmdi zmdi-assignment-account' title='Writing Consultant'></i>";
    const ICO_SERVICING = "<i class='zmdi zmdi-assignment-account'></i>";
    const ICO_SIGNATURE = "<i class='zmdi zmdi-assignment-account' title='Signature Consultant'></i>";
    const ICO_HOLDER = "<i class='zmdi zmdi-account'></i>";
    const ICO2X_HOLDER = "<i class='zmdi zmdi-account zmdi-hc-2x c-lime'></i>";
    const ICO4X_HOLDER = "<i class='zmdi zmdi-account zmdi-hc-4x c-lime'></i>";
    const ICO_INSURED = "<i class='zmdi zmdi-account c-lime'></i>";
    const ICO4X_INFORCE = "<i class='zmdi zmdi-check-circle zmdi-hc-4x c-white'></i>";
    const ICO4X_HOLIDAY = "<i class='zmdi zmdi-flower zmdi-hc-4x c-white'></i>";
    const ICO2X_EFFECTIVE = "<i class='zmdi zmdi-check-circle zmdi-hc-2x c-white'></i>";
    const ICO4X_EFFECTIVE = "<i class='zmdi zmdi-check-circle zmdi-hc-4x c-white'></i>";
    const ICO2X_NONEFFECTIVE = "<i class='zmdi zmdi-block zmdi-hc-2x c-white'></i>";
    const ICO4X_NONEFFECTIVE = "<i class='zmdi zmdi-block zmdi-hc-4x c-white'></i>";
    const ICO2X_CHECK = "<i class='zmdi zmdi-check-circle zmdi-hc-2x c-green'></i>";
    const ICO2X_CROSS = "<i class='zmdi zmdi-close-circle zmdi-hc-2x c-red'></i>";
    const ICO4X_CHECK = "<i class='zmdi zmdi-check-circle zmdi-hc-4x c-green'></i>";
    const ICO4X_CROSS = "<i class='zmdi zmdi-close-circle zmdi-hc-4x c-red'></i>";
    const ICO_REFRESH = "<i class='zmdi zmdi-refresh-sync zmdi-hc-2x'></i>";
    const ICO_NB_CRM = "<i class='zmdi zmdi-phone-end text-danger'></i>";
    const ICO_CS_CRM = "<i class='zmdi zmdi-phone-end text-success'></i>";
    const ICO_CHART = "<i class='zmdi zmdi-chart zmdi-hc-2x c-deeppurple'></i>";
    const ICO_ATTACHMENT = "<i class='zmdi zmdi-attachment-alt'></i>";
    const ICO_WIP = "<i class='zmdi zmdi-timer text-info'></i>";
    const SIGN_ACTION_MENU = "<i class='zmdi zmdi-more-vert'></i>";
    const SIGN_ADD = "<i class='zmdi zmdi-plus'></i>";
    const SIGN_ADD_CIRCLE = "<i class='zmdi zmdi-plus-circle'></i>";
    const SIGN_ADDRESS = "<span class='text-warning'><i class='zmdi zmdi-pin zmdi-hc-2x m-r-5'></i></span>";
    const SIGN_ATTACHMENT = "<i class='zmdi zmdi-attachment-alt'></i>";
    const SIGN_CHECK = "<i class='zmdi zmdi-check-circle c-green'></i>";
    const SIGN_CROSS = "<span class='c-red'><i class='zmdi zmdi-close-circle'></i></span>";
    const SIGN_CROSS_NOCOLOR = "<i class='zmdi zmdi-close-circle'></i>";
    const SIGN_CROSS_NOCOLOR_2X = "<i class='zmdi zmdi-close-circle zmdi-hc-2x'></i>";
    const SIGN_DOWNLOAD = "<i class='zmdi zmdi-download'></i></span>";
    const SIGN_DOWNLOAD_2X = "<i class='zmdi zmdi-hc-2x zmdi-download  m-t-20'></i></span>";
    const SIGN_GOBACK = "<i class='zmdi zmdi-arrow-left m-r-5'></i>";
    const SIGN_PHONE = "<span class='text-warning'><i class='zmdi zmdi-phone-end zmdi-hc-2x m-r-5'></i></span>";
    const SIGN_IDENTIFICATION = "<span class='text-warning'><i class='zmdi zmdi-pin-account zmdi-hc-2x m-r-5'></i></span>";
    const SIGN_PERFORMANCE = "<span class='text-warning'><i class='zmdi zmdi-chart m-r-5'></i></span>";
    const SIGN_PLAN = "<span class='text-warning'><i class='zmdi zmdi-folder-star m-r-5'></i></span>";
    const SIGN_PLANCHANGES = "<span class='text-warning'><i class='zmdi zmdi-file-plus m-r-5'></i></span>";
    const SIGN_FINANCIAL = "<span class='text-warning'><i class='zmdi zmdi-money m-r-5'></i></span>";
    const SIGN_CREATED_BY = "<i class='zmdi zmdi-account-add' title='Created by'></i>";
    const SIGN_CRM = "<span class='text-warning'><i class='zmdi zmdi-phone-ring m-r-5'></i></span>";
    const SIGN_CRM_2X = "<span class='text-warning'><i class='zmdi zmdi-phone-ring m-r-5 zmdi-hc-2x'></i></span>";
    const SIGN_CRM_4X = "<span class='text-warning'><i class='zmdi zmdi-phone-ring m-r-5 zmdi-hc-4x'></i></span>";
    const SIGN_CRM_ACTIVITY_4X = "<span class='text-warning'><i class='zmdi zmdi-account-box-phone zmdi-hc-4x'></i></span>";
    const SIGN_PLAN_4X = "<span class='text-warning'><i class='zmdi zmdi-folder-star m-r-5 zmdi-hc-4x'></i></span>";
    const SIGN_PUBLIC = "<i class='zmdi zmdi-globe m-r-5'></i>";
    const SIGN_CHANNEL = "<i class='zmdi zmdi-case m-r-5'></i>";
    const SIGN_CONSULTANT = "<i class='zmdi zmdi-face m-r-5'></i>";
    const SIGN_CONSULTANT_2X = "<span class='text-warning'><i class='zmdi zmdi-face zmdi-hc-2x m-r-5'></i></span>";
    const SIGN_FOR_CLIENT = "<span data-toggle='tooltip'><i class='zmdi zmdi-account text-success'></i></span>";
    const SIGN_FOR_CLIENT_2X = "<span title='Client'><i class='zmdi zmdi-account zmdi-hc-2x text-success'></i></span>";
    const SIGN_FOR_CONSULTANT = "<span data-toggle='tooltip'><i class='zmdi zmdi-account-o text-info'></i></span>";
    const SIGN_FOR_CONSULTANT_2X = "<span title='Consultant'><i class='zmdi zmdi-account-o zmdi-hc-2x text-info'></i></span>";
    const SIGN_CONSULTANT_4X = "<span class='text-warning'><i class='zmdi zmdi-face m-r-5 zmdi-hc-4x'></i></span>";
    const SIGN_CLIENT = "<span class='text-warning'><i class='zmdi zmdi-account-box m-r-5'></i></span>";
    const SIGN_CLIENT_4X = "<span class='text-warning'><i class='zmdi zmdi-account-box m-r-5 zmdi-hc-4x'></i></span>";
    const SIGN_GOTO = "<i class='zmdi zmdi-chevron-right'></i>";
    const SIGN_DOWN = "<i class='zmdi zmdi-chevron-down'></i>";
    const SIGN_USER = "<span class='text-warning'><i class='zmdi zmdi-account m-r-5'></i></span>";
    const SIGN_PRINT = "<i class=\"zmdi zmdi-print\"></i>";
    const SIGN_PRINT_2X = "<i class='zmdi zmdi-print zmdi-hc-2x m-r-5'></i>";
    const SIGN_USER_4X = "<span class='text-warning'><i class='zmdi zmdi-account m-r-5 zmdi-hc-4x'></i></span>";
    const SIGN_TRANSFER = "<span class='text-warning'><i class='zmdi zmdi-arrows m-r-5'></i></span>";
    const SIGN_DETAILS = "<i class='zmdi zmdi-dialpad m-r-5'></i>";
    const SIGN_EMAIL = "<i class='zmdi zmdi-email m-r-5' title='Email'></i> ";
    const SIGN_SMS = "<span class='text-info'><i class='zmdi zmdi-smartphone m-r-5' title='SMS'></i></span>";
    const SIGN_SPINNER = "<i class='zmdi zmdi-rotate-right zmdi-hc-spin'></i> ";
    const FORGOT_PASSWORD_TTL = 3600 * 24; // seconds
    const ACCOUNT_MIGRATION_TTL = 3600 * 24; // seconds
// THEME ICONS
    const ICO2X_CLIENT = '<span data-toggle="tooltip" data-original-title="Client"><i class="zmdi zmdi-account zmdi-hc-2x c-white"></i></span> ';
    const ICO2X_CONSULTANT = '<span data-toggle="tooltip" data-original-title="Consultant"><i class="zmdi zmdi-account-o zmdi-hc-2x c-white"></i></span> ';
//    const ICO2X_CS = '<span data-toggle="tooltip" data-original-title="Customer Service"><i class="zmdi zmdi-phone-msg zmdi-hc-2x text-warning"></i></span>';
//    const ICO2X_NB = '<span data-toggle="tooltip" data-original-title="New Businiess"><i class="zmdi zmdi-alarm-plus zmdi-hc-2x text-primary"></i></span>';
    const ICO2X_OVERDUE = '<span data-toggle="tooltip" data-original-title="Overdue"><i class="zmdi zmdi-alert-polygon zmdi-hc-2x c-white"></i></span> ';
    const ICO2X_PRODUCT = '<span data-toggle="tooltip" data-original-title="Product"><i class="zmdi zmdi-money zmdi-hc-2x c-white"></i></span> ';
    const ICO2X_USER = '<span data-toggle="tooltip" data-original-title="User"><i class="zmdi zmdi-account zmdi-hc-2x c-white"></i></span> ';
//    const ICO4X_CRM = '<i class="zmdi zmdi-account-box-phone zmdi-hc-4x text-warning"></i>';
    const ICO_CHECK = '<i class="zmdi zmdi-check-circle zmdi-hc-2x"></i>';
//    const ICO_CRM = '<i class="zmdi zmdi-account-box-phone zmdi-hc text-warning"></i>';
    const ICO_CROSS = '<button class="btn bgm-red btn-icon waves-effect waves-circle waves-float"><i class="zmdi zmdi-close"></i></button>';
//    const ICO_DOCUMENT = '<i class="zmdi zmdi-file zmdi-hc-2x text-warning"></i>';
//    const ICO_FOLDER = '<i class="zmdi zmdi-case zmdi-hc-2x c-teal pointer"></i>';
//    const ICO_GOBACK = '<button class="btn bgm-green btn-icon waves-effect waves-circle waves-float"><i class="zmdi zmdi-arrow-left"></i></button>';
    const ICO_MINUS = ' <i class="zmdi zmdi-minus c-red"></i> ';
    const ICO_OVERDUE = '<span data-toggle="tooltip" data-original-title="Overdue"><i class="zmdi zmdi-alert-polygon c-white"></i></span> ';
    const ICO_PLUS = ' <i class="zmdi zmdi-plus text-green"></i> ';
    const REDSTAR = "<span class='alert'> * </span>";
    const SIGN_ACTIVATE = '<i class="zmdi zmdi-badge-check"></i>';
//    const SIGN_ADDRESS = '<span class="text-warning"><i class="zmdi zmdi-home"></i></span>';
    const SIGN_ALARM = '<i class="zmdi zmdi-alarm"></i>';
    const SIGN_ALERT = '<i class="zmdi zmdi-alert-triangle"></i>';
    const SIGN_ALERT_2X = '<i class="zmdi zmdi-alert-triangle zmdi-hc-2x"></i>';
    const SIGN_APPROVAL = '<i class="zmdi zmdi-play-circle"></i>';
    const SIGN_BACK = '<i class="zmdi zmdi-arrow-left"></i>';
    const SIGN_BACK_2X = '<i class="zmdi zmdi-arrow-left zmdi-hc-2x"></i>';
    const SIGN_CARETRIGHT = '<i class="zmdi zmdi-caret-right"></i> ';
    const SIGN_CHECK_1X = '<i class="zmdi zmdi-check-circle text-success"></i>';
    const SIGN_CHECK_2X = '<i class="zmdi zmdi-check-circle zmdi-hc-2x text-success"></i>';
    const SIGN_CHECK_4X = '<i class="zmdi zmdi-check-circle zmdi-hc-4x text-success"></i>';
    const SIGN_CHECKOUT = '<i class="zmdi zmdi-case-check"></i>';
    const SIGN_CHECK_NOCOLOR = '<i class="zmdi zmdi-check-circle zmdi-hc-2x"></i>';
    const SIGN_CLOSE = '<i class="zmdi zmdi-close-circle zmdi-hc-2x"></i>';
    const SIGN_CREATE_1X = '<span class="text-green"><i class="zmdi zmdi-plus-circle"></i></span>';
    const SIGN_CROSS_1X = '<i class="zmdi zmdi-close-circle text-danger"></i>';
    const SIGN_CROSS_2X = '<i class="zmdi zmdi-close-circle text-danger zmdi-hc-2x"></i>';
    const SIGN_CROSS_4X = '<i class="zmdi zmdi-close-circle zmdi-hc-4x text-danger"></i>';
    const SIGN_DEACTIVATE = '<i class="zmdi zmdi-block"></i>';
    const SIGN_DEACTIVATE_2X = '<i class="zmdi zmdi-block zmdi-hc-2x"></i>';
    const SIGN_DELETE = '<i class="zmdi zmdi-close-circle text-danger"></i>';
    const SIGN_DELETE_1X = '<i class="zmdi zmdi-close-circle"></i>';
    const SIGN_DELETE_2X = '<i class="zmdi zmdi-close-circle zmdi-hc-2x text-danger"></i>';
    const SIGN_REPLY_1X = '<i class="fa fa-reply" aria-hidden="true"></i>';
    const SIGN_REPLY_2X = '<i class="fa fa-reply fa-2x" aria-hidden="true"></i>';
    const SIGN_SAVE_AS = '<i class="zmdi zmdi-sign-in"></i>';
    const SIGN_TRASH_1X = '<i class="zmdi zmdi-delete"></i>';
    const SIGN_TRASH_2X = '<i class="text-danger zmdi zmdi-delete zmdi-hc-2x"></i>';
//    const SIGN_DETAILS = '<span class="text-warning"><i class="zmdi zmdi-info"></i></span>';
    const SIGN_DOCUMENT = '<span class="text-warning"><i class="zmdi zmdi-bookmark "></i></span>';
//    const SIGN_EMAIL = '<span class="text-warning"><i class="zmdi zmdi-email"></i></span>';
//    const SIGN_FINANCIAL = '<span class="text-warning"><i class="zmdi zmdi-money"></i></span>';
//    const SIGN_FOR_CLIENT = '<span data-toggle="tooltip" data-original-title="For Client"><i class="zmdi zmdi-account text-danger"></i></span>';
//    const SIGN_FOR_CLIENT_2X = '<span data-toggle="tooltip" data-original-title="For Client"><i class="zmdi zmdi-account zmdi-hc-2x text-danger"></i></span>';
//    const SIGN_FOR_CONSULTANT = '<span data-toggle="tooltip" data-original-title="For Consultant"><i class="zmdi zmdi-account-o text-danger"></i></span>';
//    const SIGN_FOR_CONSULTANT_2X = '<span data-toggle="tooltip" data-original-title="For Consultant"><i class="zmdi zmdi-account-o zmdi-hc-2x text-danger"></i></span>';
//    const SIGN_IDENTIFICATION = '<span class="text-warning"><i class="zmdi zmdi-card"></i></span>';
    const SIGN_IMAGE = '<span class="text-warning"><i class="zmdi zmdi-image-o"></i></span>';
    const SIGN_LOCKED = '<i class="zmdi zmdi-lock"></i>';
    const SIGN_MODIFY = '<i class="zmdi zmdi-edit"></i>';
    const SIGN_MODIFY_2X = '<i class="zmdi zmdi-edit zmdi-hc-2x"></i>';
    const SIGN_MODIFY_4X = '<span class="c-red"><i class="zmdi zmdi-edit zmdi-hc-4x"></i></span>';
    const SIGN_PROJECT = '<i class="zmdi zmdi-trending-up"></i>';
    const SIGN_OPEN = '<span class="text-warning"><i class="zmdi zmdi-open-in-new"></i></span>';
    const SIGN_OVERDUE_BLINK = ' <span class="text-danger blink"><i class="zmdi zmdi-circle"></i></span> ';
    const SIGN_PIN = '<i class="zmdi zmdi-pin"></i>';
    const SIGN_PERMISSION = '<span class="text-warning"><i class="zmdi zmdi-shield-security"></i></span>';
//    const SIGN_PHONE = '<span class="text-warning"><i class="zmdi zmdi-phone"></i></span>';
    const SIGN_POLICY = '<i class="zmdi zmdi-bookmark"></i>';
//    const SIGN_PLANCHANGES = '<span class="text-warning"><i class="zmdi zmdi-file-plus"></i></span>';
//    const SIGN_PLAN_4X = '<span class="text-warning"><i class="zmdi zmdi-folder-star zmdi-hc-4x"></i></span>';
    const SIGN_PLUS = ' <i class="zmdi zmdi-plus text-green"></i> ';
    const SIGN_PRODUCT = '<span class="text-warning"><i class="zmdi zmdi-close-circle"></i></span>';
    const SIGN_RELATIONSHIP = '<span class="text-warning"><i class="zmdi zmdi-share"></i></span>';
    const SIGN_REMINDER = '<i class="zmdi zmdi-alarm"></i>';
    const SIGN_SAVE = '<i class="zmdi zmdi-save"></i>';
    const SIGN_SAVE_2X = '<i class="zmdi zmdi-save zmdi-hc-2x"></i>';
    const SIGN_SAVE_DANGER = '<i class="zmdi zmdi-save text-danger" title="Save"></i>';
//    const SIGN_TRANSFER = '<span class="text-warning"><i class="zmdi zmdi-arrows"></i></span>';
    const SIGN_SETTING = '<i class="zmdi zmdi-settings"></i>';
    const SIGN_STAR = '<i class="zmdi zmdi-star"></i>';
    const SIGN_TRANSFER_IN = ' <i class="zmdi zmdi-caret-left-circle text-danger" title="Transfer in"></i> ';
//    const SIGN_USER_4X = '<span class="text-warning"><i class="zmdi zmdi-account zmdi-hc-4x"></i></span>';
    const SIGN_VALUATION = '<span class="text-warning"><i class="zmdi zmdi-trending-up"></i></span>';
    const SIGN_VIEW = '<i class=\'zmdi zmdi-eye\'></i>';
    const LEVEL1 = "<span class=\"step red-dark\">1</span>";
    const LEVEL2 = "<span class=\"step green-dark\">2</span>";
    const LEVEL3 = "<span class=\"step blue-dark\">3</span>";
    const LEVEL4 = "<span class=\"step amber-dark\">4</span>";
    const CS_ICON = "<i class='zmdi zmdi-hand-o-right text-danger'></i>&nbsp;";
    const NB_ICON = "<i class='zmdi zmdi-star text-danger'></i>&nbsp;";
    const SHOW_PENDING = 1;
    const SHOW_PENDING_NO = 0;
// XMcrypt key & iv for tripledes
    const ENCKEY = "4bZBFcvoSr9PoantdZxSfn4Y";
    const IV = "zmdizmdi";
// DMS Top level libraries
    const DMS_FRONTEND_CLIENT = 2;
    const DMS_FRONTEND_PRODUCT = 3;
    const DMS_PLAN = 4;
    const DMS_PEOPLE = 5;
    const DMS_PRODUCT = 6;
    const DMS_RIDER = 7;
    const ENTITY_COMPANY = 1;
    const ENTITY_BRANCH = 2;
    const ENTITY_DIRECTOR = 3;
    const ENTITY_INDIVIDUAL = 4;
// LOGICS
    const REQUIRED = 1;
// DATE FORMAT
    const DATETIMEFORMAT = "Y-m-d H:i:s";
    const DATEFORMAT = "Y-m-d";
    const DATETIMEFORMAT1 = "Y-m-d H:i";
// user_role_type
    const URT_CLIENT = 2;
    const URT_CONSULTANT = 3;
    const URT_OP = 4;
    const URT_ADMIN = 1;
// plan_people_type
    const HOLDER = 1;
    const WRITING = 2;
    const SERVICING = 3;
    const SIGNATURE = 4;
    const INSURED = 5;
// plan_change table->change_type
    const TOPUP = 1;
    const TOPDOWN = 2;
    const LUMPSUM_TOPUP = 3;
    const LUMPSUM_TOPUP_SAVINGS = 4;
    const RIDER = 5;
    const WITHDRAWAL = 6;
    const RIDERDECREASE = 7;
    const IIE = 8;
    const IIEDECREASE = 9;
    const RIDERIIE = 10;
    const RIDERIIEDECREASE = 11;
// payment_fqy
    const FQY_ANNUAL = 1;
    const FQY_MONTHLY = 2;
    const FQY_QUARTERLY = 3;
    const FQY_SEMI_ANNUALLY = 4;
    const FQY_LUMPSUM = 5;
    const FQY_NA = 6;
// plan change table
    const PLAN_CHANGE_INFORCE = 1;
    const PLAN_CHANGE_CANCELLED = 0;
// crm_record->crm_cat
    const CRM_CS = 2;
    const CRM_NB = 1;
    const CRM_LEAD = 3;
// progress
    const CRM_CANCEL = 0;
    const CRM_NEW = 1;
    const CRM_WIP = 5;
    const CRM_COMPLETED = 9;
// people_role_type
    const CLIENT = 1;
    const CONSULTANT = 2;
    const INTERNAL = 3;
// product status category
    const PIPELINE = 1;
    const EFFECTIVE = 2;
    const NON_EFFECTIVE = 3;
// people_type
    const PT_AGENT1 = 1; // Desk Head
    const PT_AGENT2 = 2; // Service Desk
    const PT_AGENT3 = 3;
    const PT_AGENT4 = 4;
    const PT_AGENT5 = 5;
    const PT_AGENT6 = 6;
    const PT_AGENT7 = 7;
    const PT_AGENT8 = 8;
    const PT_AGENT9 = 9;
    const PT_AGENT10 = 10;
    const PT_CLIENT = 11;
    const PT_STAFF = 12;
    const PT_COMPANY = 15;
    const PT_CONTACTPOINT = 21;
    const PT_PROVIDER_PEOPLE = 22;
    const COUNTRY_UNKNOWN = 248;
    const CSRFP_SECRET = "fompj9538947gn8vDSEvug589EDW5";

    public static function arrayToInClause($array) {
        $return = "";

        if (count($array) > 0 && is_array($array)) {
            foreach ($array as $item) {
                $return .= $item . ", ";
            }
            $return = trim($return, ", ");

            $return = "(" . $return . ")";
        } else {
//        var_dump($array); die();
            $return = "('-1')";
        }
        return $return;
    }

    /**
     * Compares if date1 is <= date2 by PHP strtotime
     * if date1 <= date2 returns TRUE
     * if date2 = null returns TRUE
     * @param string $date1
     * @param string $date2
     * @return boolean
     *          
     *          
     */
    static function compareDates($date1, $date2) {
        if (!$date2)
            return TRUE;

//        $dtDate1 = \DateTime::createFromFormat(self::DATEFORMAT, $date1);
//        $dtDate2 = \DateTime::createFromFormat(self::DATEFORMAT, $date2);
        $dtDate1 = strtotime($date1);
        $dtDate2 = strtotime($date2);

        if ($dtDate1 <= $dtDate2) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function mask($str, $start = 0, $length = null) {
        $mask = preg_replace("/\S/", "*", $str);
        if (is_null($length)) {
            $mask = substr($mask, $start);
            $str = substr_replace($str, $mask, $start);
        } else {
            $mask = substr($mask, $start, $length);
            $str = substr_replace($str, $mask, $start, $length);
        }
        return $str;
    }

    public static function file_upload_max_size($unit) {
        static $max_size = -1;

        if ($max_size < 0) {
// Start with post_max_size.
            $max_size = self::parse_size(ini_get('post_max_size'));

// If upload_max_size is less, then reduce. Except if upload_max_size is
// zero, which indicates no limit.
            $upload_max = self::parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        switch ($unit) {
            case "byte":
                return $max_size;
                break;
            case "kb":
                return $max_size / 1024;
                break;
            case "mb":
                return $max_size / 1024 / 1024;
                break;
            default:
                return $max_size;
                break;
        }
    }

    public static function parse_size($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    public static function genFileName($len = 64, $case = false, $table = "0123456789abcedefghjiklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") {
        return self::genUuid();
        $temp = null;
        for ($i = 0; $i < 100; $i++) { // try 100 times
            for ($x = 0; $x < $len; $x++) {
                if (!$case) {
                    $temp .= substr($table, rand(0, strlen($table)), 1);
                } else {
                    $char = substr($table, rand(0, strlen($table)), 1);
                    $temp .= ((rand(0, 3) == 0) ? strtoupper($char) : $char);
                }
            }
            if (trim(strlen($temp)) == $len) { // sometimes this returns strlen=7 only
                return $temp;
            }
            $temp = "";
        }
        return $temp;
    }

    public static function genRandomKey($len = 10, $case = false, $table = "0123456789abcedefghjikmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ") {
        $temp = null;

        for ($i = 0; $i < 100; $i++) { // try 100 times
            for ($x = 0; $x < $len; $x++) {
                if (!$case) {
                    $temp .= substr($table, rand(0, strlen($table)), 1);
                } else {
                    $char = substr($table, rand(0, strlen($table)), 1);
                    $temp .= ((rand(0, 3) == 0) ? strtoupper($char) : $char);
                }
            }
            if (trim(strlen($temp)) == $len) { // sometimes this returns strlen=7 only
                return $temp;
            }
            $temp = "";
        }
        return $temp;
    }

    public static function getYearsArray($from, $to) {
        $results = [];
        for ($i = $from; $i < $to; $i++) {
            $results[] = array(
                "id" => $i,
                "itemname" => $i
            );
        }
        return $results;
    }

//xss mitigation functions
    public static function xssafe($data, $encoding = 'UTF-8') {
        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML401, $encoding);
    }

    static function array2SqlIn($array) {
        $return = "";

        if ($array) {
            foreach ($array as $i => $item) {
                $return .= "'" . $item . "',";
            }
        }
        $return = rtrim($return, ",");
        $return = "(" . $return . ")";
        return $return;
    }

    /**
     * 
     * @param type $modelErrors
     * @param type $planText
     * @return type
     */
    static function modelErrorHtml($modelErrors, $useHtml = TRUE) {
        $html = "";

        if (!$modelErrors) {
            return null;
        }

        foreach ($modelErrors as $errors) {
            if (is_array($errors)) {
                foreach ($errors as $error) {
                    $html .= "$error<br>\r\n";
                }
            } else {
                $html .= "$errors<br>\r\n";
            }
        }
        return $html;
    }

    public static function genPDF($content, $lang = "eng", $orientation = null, $title = null) {
        $fsFolder = Yii::$app->params['dmsPath'] . 'pdf/';

        if (is_dir($fsFolder) == false) {
            mkdir($fsFolder, 0775);  // Create directory if it does not exist
        }

        $filename = time() . '_' . Yii::$app->security->generateRandomString(32) . '.pdf';

        $pdf = new \kartik\mpdf\Pdf([
            'filename' => Yii::$app->params['dmsPath'] . 'pdf/' . $filename,
            // set to use core fonts only
            'mode' => Pdf::MODE_UTF8,
            // A4 paper format
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => (!$orientation ? Pdf::ORIENT_PORTRAIT : $orientation),
            // stream to browser inline
//'destination' => Pdf::DEST_BROWSER,
            'destination' => Pdf::DEST_FILE,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
// enhanced bootstrap css built by Krajee for mPDF formatting
//            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
// any css to be embedded if required
            'cssInline' => '
.text-center {
    text-align: center;
}
.table-sm {
    margin-bottom: 30px;
}
.table {
    width: 100%;
    max-width: 100%;
    margin-bottom: 18px;
    font-size: 12px;
}
.text-primary {
    color: #009688;
}
.text-danger {
    color: #f6675d;
}
.text-info {
    color: #31708f;
}
.lead {
    font-size: 16px;
}
th, td {
    padding: 4px 10px !important;
}
            ',
            // set mPDF properties on the fly
            'options' => [
                'title' => $title ? $title : '', //WebLang::t('commission', $lang) . WebLang::t('sp', $lang) . WebLang::t('statement', $lang),
                'autoLangToFont' => true,
                'autoScriptToLang' => true,
                'autoVietnamese' => true,
                'autoArabic' => true,
            ],
            // call mPDF methods on the fly
            'methods' => [
//                'SetHeader' => [WebLang::getByLocaleId('commission', $lang) . WebLang::getByLocaleId('sp', $lang) . WebLang::getByLocaleId('statement', $lang)],
                'SetFooter' => [WebLang::t('page', $lang) . WebLang::t('sp', $lang) . '{PAGENO}'],
            ]
        ]);

        $pdf->render();

        return $filename;
    }

    public static function genUuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),
                // 16 bits for "time_hi_and_version",
// four most significant bits holds version number 4
                mt_rand(0, 0x0fff) | 0x4000,
                // 16 bits, 8 bits for "clk_seq_hi_res",
// 8 bits for "clk_seq_low",
// two most significant bits holds zero and one for variant DCE1.1
                mt_rand(0, 0x3fff) | 0x8000,
                // 48 bits for "node"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * 
     * @param type $array
     * @return type
     */
    public static function genSingingToken($array = null) {
        $singer = new \common\helpers\Singer;

        if ($array) {
            foreach ($array as $key => $value) {
                $singer->addKeyValue($key, $value);
            }
        }
        return $singer->getSignature();
    }

    /**
     * 
     * @param type $array BEWARE of the value data type
     * @param type $token
     * @return boolean
     */
    public static function verifySingingToken($array, $token) {
        $singer = new \common\helpers\Singer;

        if ($array) {
            foreach ($array as $key => $value) {
                $singer->addKeyValue($key, $value);
            }
        }

        if ($singer->validateSignature($token)) {
            return TRUE;
        }
        return FALSE;
    }

    public static function showXAgo($timestampToDisplay) {
        return ( $timestampToDisplay < 60 * 60 * 24 * 365) ?
                Yii::$app->formatter->asRelativeTime($timestampToDisplay) : Yii::$app->formatter->asDate($timestampToDisplay);
    }

    public static function showX1Ago($timestampToDisplay) {
        return Yii::$app->formatter->asRelativeTime(date_create_from_format(self::DATETIMEFORMAT, $timestampToDisplay));
    }

    public static function processBBSPost($content) {
        preg_match("/\[plan\:[0-9]+\]/i", $content, $matches);
        if ($matches) {
            foreach ($matches as $match) {
                preg_match("/([0-9]+)/", $match, $idplan);
                $planTag = "<a class='text-danger fu-700' href='" . yii\helpers\Url::to(['plan/view']) . "?id=" . $idplan[0] . "'>" . $idplan[0] . "</a>";
                $content = preg_replace("/\[plan\:[0-9]+\]/i", $planTag, $content);
            }
        }

        preg_match("/\[client\:[0-9]+\]/i", $content, $matches);
        if ($matches) {
            foreach ($matches as $match) {
                preg_match("/([0-9]+)/", $match, $idpeople);
                $clientTag = "<a class='text-danger fu-700' href='" . yii\helpers\Url::to(['client/view']) . "?idpeople=" . $idpeople[0] . "'>" . $idpeople[0] . "</a>";
                $content = preg_replace("/\[client\:[0-9]+\]/i", $clientTag, $content);
            }
        }
        preg_match("/\[consultant\:[0-9]+\]/i", $content, $matches);
        if ($matches) {
            foreach ($matches as $match) {
                preg_match("/([0-9]+)/", $match, $idpeople);
                $clientTag = "<a class='text-danger fu-700' href='" . yii\helpers\Url::to(['consultant/view']) . "?idpeople=" . $idpeople[0] . "'>" . $idpeople[0] . "</a>";
                $content = preg_replace("/\[consultant\:[0-9]+\]/i", $clientTag, $content);
            }
        }

        $content = str_replace("&quot;", "'", $content);
        $content = str_replace('"', "'", $content);
//        $content = htmlspecialchars($content);

        return $content;
    }

    public static function getExcerpt($str, $maxLength = 100, $startPos = 0) {

        $strArray = self::mbStringToArray($str);
        $return = "";

        if (self::containsAnyMultibyte($str)) {
            $maxLength = round($maxLength / 2);
        }

        if (count($strArray) > $maxLength) {
            for ($i = 0; $i < $maxLength; $i++) {
                $return .= $strArray[$i];
            }
            $return .= '...';
        } else {
            $return = $str;
        }
        return $return;
    }

    public static function mbStringToArray($string) {
# Split at all position not after the start: ^ 
# and not before the end: $ 
        return preg_split('/(?<!^)(?!$)/u', $string);
    }

    public static function containsAnyMultibyte($string) {
        return !mb_check_encoding($string, 'ASCII') && mb_check_encoding($string, 'UTF-8');
    }

    public static function checkPermission($permission) {
//        if (Yii::$app->user->identity->checkPermission($permission))
            return true;
//        else
//            return false;
    }

    public static function breakParagraph($string, $max = 60) {
        $in_pieces = array();

        while (strlen($string) > $max) {
// location of last space in the first $max characters
            $substring = substr($string, 0, $max);
// pull from position 0 to $substring position
            $in_pieces[] = trim(substr($substring, 0, strrpos($substring, ' ')));
// $string (haystack) now = haystack with out the first $substring characters
            $string = substr($string, strrpos($substring, ' '));
// UPDATE (2013 Oct 16) - if remaining string has no spaces AND has a string length
//  greater than $max, then this will loop infinitely!  So instead, just have to bail-out (boo!)
            if (strlen($string) > $max)
                break;
        }
        $in_pieces[] = trim($string); // final bits o' text

        $return = "";
        foreach ($in_pieces as $line) {
            $return .= $line . "<br>";
        }
        return $return;
    }

    static function timestamp($minutes_to_add) {
        if ($minutes_to_add < 0)
            return false;

        if (!$minutes_to_add || $minutes_to_add == 0)
            return date(self::DATETIMEFORMAT);

        $time = new \DateTime(date(self::DATETIMEFORMAT));
        $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
        $stamp = $time->format(self::DATETIMEFORMAT);

        return $stamp;
    }

    static function calcDate($daysToAdd) {
        if ($daysToAdd < 0)
            return false;
        if ($daysToAdd == 0)
            return date(self::DATEFORMAT);

        $time = new \DateTime(date(self::DATEFORMAT));
        $time->add(new \DateInterval('P' . $daysToAdd . 'D'));
        $stamp = $time->format(self::DATEFORMAT);

        return $stamp;
    }

    /**
     * @param string $modifier "7D"=7 days
     * @param string $date
     * @return string
     */
    static function adjustDate($modifier, $date = null) {
        if (!$date) {
            $time = new \DateTime(date(self::DATEFORMAT));
        } else {
            $time = \DateTime::createFromFormat(self::DATEFORMAT, $date);
//            $time = new \DateTime(date(self::DATEFORMAT, $date));
        }
        if (!$time) {
            var_dump($modifier);
            var_dump($date);
            dd($time);
        }

        $time->add(new \DateInterval('P' . $modifier));
        $stamp = $time->format(self::DATEFORMAT);

        return $stamp;
    }

    /**
     * @param string $modifier "7D"=7 days
     * @param string $date
     * @return string
     */
    static function adjustDateTime($modifier, $date = null) {
        if (!$date) {
            $time = new \DateTime(date(self::DATETIMEFORMAT));
        } else {
            $time = \DateTime::createFromFormat(self::DATETIMEFORMAT, $date);
//            $time = new \DateTime(date(self::DATETIMEFORMAT, $date));
        }
//            dd($time);

        $time->add(new \DateInterval('P' . $modifier));
        $stamp = $time->format(self::DATETIMEFORMAT);

        return $stamp;
    }

    public static function sslCrypt($string, $action = 'e') {
// you may change these values to your own
        $secret_key = 'my_simple_secret_key';
        $secret_iv = 'my_simple_secret_iv';

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'e') {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        } else if ($action == 'd') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }

    /**
     * Returns array of ['id', 'name'] of holders, insured (planPeople model)
     * @param array $models
     * @return type
     */
    public static function mapPlanPeopleName($models) {
        $result = [];
        foreach ($models as $model) {
            $key = $model->idpeople;
            if ($model->people) {
                $value = $model->people->name() . " (" . $model->people->name('cht') . ")";
                $result[$key] = $value;
            } else {
                $result[$key] = "";
            }
            return $result;
        }
    }

    /**
     * 
     * @param type $class
     * @param type $icon
     * @param type $webLangId
     * @return string
     */
    public static function icon($class, $icon, $webLangId = null) {
        $return = "<span";

        if ($class) {
            $return .= " class='$class'";
        }

        if ($webLangId) {
            $return .= " title='" . WebLang::t($webLangId) . "'";
        }
        $return .= ">";
        $return .= $icon . "</span>";

        return $return;
    }

    public static function __fgetcsv(&$handle, $length = null, $d = ",", $e = '"') {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = false;
        while ($eof != true) {
            $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
            if ($itemcnt % 2 == 0) {
                $eof = true;
            }
        }

        $_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));

        $_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];

        for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++) {
            $_csv_data[$_csv_i] = preg_replace("/^" . $e . "(.*)" . $e . "$/s", "$1", $_csv_data[$_csv_i]);
            $_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
        }

        return empty($_line) ? false : $_csv_data;
    }

    static function datetime($string) {
        return substr($string, 0, 16);
    }

    static function docTypeName($fileType) {

        if (!$fileType || empty(trim($fileType))) {
            return WebLang::t('unknown');
        }
        switch ($fileType) {
            case "application/pdf":
                return "PDF";
                break;
            case "image/jpeg":
                return "jpg";
                break;
            default:
                return WebLang::t('document');
                break;
        }
    }

    public static function StringInputCleaner($data) {
        //remove space before and after
        $data = trim($data);
        //remove slashes
        $data = stripslashes($data);
        $data = (filter_var($data, FILTER_SANITIZE_STRING));
        return $data;
    }

    /**
     * Converts seconds to human readable format (H:i:s) string.
     * @param int $seconds
     * @param bool $hideSeconds
     * @static
     * @return string
     */
    public static function getHmsFromSeconds($duration, $hideSeconds = false) {
        list($hours, $minutes, $seconds) = static::getHmsParts($duration);
        $hm = str_pad($hours, 2, "0", STR_PAD_LEFT);
        $hm .= ':' . str_pad($minutes, 2, "0", STR_PAD_LEFT);
        if (!$hideSeconds) {
            $hm .= ':' . str_pad($seconds, 2, "0", STR_PAD_LEFT);
        }
        return $hm;
    }

    /**
     * Converts duration in seconds to pretty human readable format (1h 12min 45s)
     * @param int $duration Duration in seconds
     * @param bool $hideSeconds
     * @return mixed
     */
    public static function getPrettyHms($duration, $hideSeconds = false) {
        $duration += 1;
        list($hours, $minutes, $seconds) = static::getHmsParts($duration);
        $labelParts = [];
        if ($hours) {
            $labelParts[] = $hours . 'h';
        }
        if ($minutes) {
            $labelParts[] = $minutes . 'min';
        }
        if ($seconds && !$hideSeconds) {
            $labelParts[] = $seconds . 'sec';
        }
        return implode(' ', $labelParts);
    }

    /**
     * Divides duration in seconds into hours, minutes, seconds
     * @param int $duration
     * @return array
     */
    protected static function getHmsParts($duration) {
        $duration = intval($duration);
        $hours = intval($duration / 3600);
        $minutes = intval(($duration / 60) % 60);
        $seconds = intval($duration % 60);
        return [$hours, $minutes, $seconds];
    }

    public static function planCheckpointHtml($checkpoint) {
        switch ($checkpoint) {
            case 0:
                $css = "danger";
                break;
            case 1:
                $css = "info";
                break;
            case 2:
                $css = "warning";
                break;
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                $css = "success";
                break;
        }
        return "<label class='label label-$css pointer' title='" . WebLang::t("checkpoint$checkpoint") . "'>$checkpoint" . '</label>';
    }

    /**
     * 
     * @param float $float
     * @param int $neededPrecision
     * @param int $startAt default=7
     * @return float
     */
    public static function roundFloat($float, $neededPrecision, $startAt = 7) {

        if ($neededPrecision < $startAt) {
            $startAt--;
            $newFloat = round($float, $startAt);
            return self::roundFloat($newFloat, $neededPrecision, $startAt);
        }

        return $float;
    }

    public static function roundStr($number, $decimals = null) {
        $previous_precision = ini_get('precision');
        ini_set('precision', 12);

        if (!$decimals) {
            $decimals = \common\models\Settings::$key['decimals'];
        }
        $return = round($number, $decimals);
//        ini_set('precision', $previous_precision);
        return $return;
    }

    public static function round($number, $precision = null) {
        if (!$precision) {
            $precision = Settings::$key['decimals'];
        }
        return number_format((float) $number, $precision, '.', '');
    }

    public static function clean($string) {
        return \yii\helpers\HtmlPurifier::process($string);
    }

    public static function getIdclients($user) {
        $idclients = UserClientRelationship::find()
                ->select(['idclient'])
                ->where(['status' => 1])
                ->andwhere(['iduser' => $user])
                ->column();
        return $idclients;
    }

    public static function getIdClient($user) {
        $idclient = UserClientRelationship::find()
                ->select(['idclient'])
                ->where(['status' => 1])
                ->andwhere(['iduser' => $user])
                ->one();
        return $idclient;
    }

    public static function getLang() {
        $lang = Yii::$app->language;

        if (strstr($lang, 'en-')) {
            $return = "eng";
        } else {
            $return = "cht";
        }
        return $return;
    }

    static function fixDateFormat($string) {
        $date = \DateTime::createFromFormat('Y-m-d', $string);
        return $date->format('Y-m-d');
    }

}
