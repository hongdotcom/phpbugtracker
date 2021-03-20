<?php

namespace common\helpers;

//use common\models\StaticMsg;
use common\models\WebLang;
use Yii;
use yii\web\Response;

class ApiHandler {

    private static function jsonOutput($data) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        \Yii::$app->response->data = $data;
        // starting from Yii2 2.0.14, cannot use echo in a controller
//        echo json_encode($data);
        Yii::$app->end();
        exit();
    }

    public static function success($data = NULL) {
        self::jsonOutput([
            'data' => $data,
            'success' => TRUE,
        ]);
        Yii::$app->end();
        exit();
    }

    /**
     * 
     * @param type $msg
     * @param type $errorCode
     */
    public static function failure($msg = NULL, $errorCode = NULL) {

        $finalMsg = WebLang::t('operation_error');

        if ($msg <> NULL) {
            // insert a blank line first
            $finalMsg .= "<br><br>";

            if (is_array($msg)) {
                foreach ($msg as $msgItem) {
                    $finalMsg .= "<p>" . $msgItem . "</p>";
                }
            } else {
                $finalMsg .= "<p>" . $msg . "</p>";
            }
        }
        if ($errorCode <> NULL) {
            $finalMsg .= "<h4 class='text-danger'>Code " . $errorCode . "</h4>";
        }
        self::jsonOutput([
            'success' => FALSE,
            'msg' => $finalMsg,
            'errorcode' => $errorCode,
        ]);
        Yii::$app->end();
        exit();
    }

    /**
     * 
     * @param string|array $msg
     * @param int $errorCode
     */
    public static function error500($msg = NULL, $errorCode = NULL) {

        $output['error'] = WebLang::t('internal_error');
        if ($msg) {
            $output['error'] = $msg;
        }

        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output);
        exit;
    }

    /**
     * 
     * @param type $msg
     * @param type $errorCode
     */
    public static function unauthorised($msg = NULL, $errorCode = NULL) {
        self::jsonOutput([
            'success' => FALSE,
            'msg' => $msg == NULL ? WebLang::t('unauthorised_access') : $msg,
            'errorcode' => $errorCode,
        ]);
        exit();
    }

    /**
     * 
     * @param type $data
     */
    public static function custom($data) {
        self::jsonOutput($data);
        exit();
    }

    public static function successRaw($data = NULL) {
        Yii::$app->response->format = Response::FORMAT_RAW;
        echo json_encode($data);
//        Yii::$app->end();
        exit();
    }

    /**
     * 
     * @param string|array $msg
     * @param int $errorCode
     */
    public static function success200($msg = null) {
        http_response_code(200);
//        header('Content-Type: application/json; charset=utf-8');
        if ($msg) {
            echo json_encode($msg);
        }
        Yii::$app->end();
    }

    public static function success200NoJson($msg) {
        http_response_code(200);
        echo $msg;
        Yii::$app->end();
    }

}
