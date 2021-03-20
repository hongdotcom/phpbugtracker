<?php

namespace frontend\controllers;

use Yii;
use common\controllers\MasterController;
use common\models\WebLang;

class AifinController extends MasterController {

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex() {

        $this->layout = "fullwidth";
        return $this->render('index', [
        ]);
    }

}
