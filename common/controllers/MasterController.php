<?php

namespace common\controllers;

use common\models\WebLang;
use Yii;
use yii\web\Controller;
use common\models\Settings;
use yii\helpers\Url;

class MasterController extends Controller {

//    public function beforeAction($action) {
//        $controller = Yii::$app->controller->id;
//
//        if (Yii::$app->user->isGuest) {
//            if ($controller == "site" && $action->id == "login") {
//                return parent::beforeAction($action);
//            }
//            return $this->redirect(Url::to(['site/login']));
//        }
//        return parent::beforeAction($action);
//    }
//
    protected function success($msg = NULL, $returnPath = NULL) {
        return $this->render('@common/views/success', [
                    'msg' => $msg,
                    'returnPath' => $returnPath,
                    'defaultMsg' => WebLang::$t['success']
        ]);
    }

    protected function internalServerError($msg = NULL) {
        if ($msg == NULL)
            $msg = WebLang::$t['internal_error'];

        throw new ServerErrorHttpException($msg);
    }

    protected function notFoundError($msg = NULL) {
        if ($msg == NULL)
            $msg = WebLang::$t['no_matching_record'];

        throw new NotFoundHttpException($msg);
    }

    protected function forbiddenError($msg = NULL) {
        if ($msg == NULL)
            $msg = WebLang::$t['no_permission'];

        throw new ForbiddenHttpException($msg);
    }

    protected function isAjax() {
        if (!Yii::$app->request->isAjax)
            $this->internalServerError();
    }

    protected function unauthorisedError($message = NULL) {
        if ($message == NULL) {
            $message = WebLang::t('no_permission');
        }
//        $this->layout = "@frontend/views/layouts/login";

        Yii::$app->response->data = $this->render("@common/views/error", ['message' => $message]);
        Yii::$app->end();
    }

    protected function operationError($msg = NULL) {

        if ($msg == NULL) {
            $msg = WebLang::t('operation_error');
        }

        echo $this->render("@common/views/error", ['message' => $msg]);
        Yii::$app->end();
    }

    public function init() {
        parent::init();
        Yii::$app->language = "eng";

        if (isset(Yii::$app->request->cookies['lang']->value)) {
            Yii::$app->language = Yii::$app->request->cookies['lang']->value;
        }

        $lang = Yii::$app->request->get('lang');

        if ($lang && in_array($lang, ['eng', 'cht', 'chs', 'jap'])) {
            Yii::$app->language = $lang;

            $languageCookie = new Cookie([
                'name' => 'lang',
                'value' => $lang,
                'expire' => time() + 60 * 60 * 24 * 365, // 365 days
            ]);
            Yii::$app->response->cookies->add($languageCookie);
        } else {
            $lang = Yii::$app->language;
        }

        Settings::populate();
        WebLang::translateLang();
        Yii::$app->view->params['t'] = WebLang::$t;
    }

}
