<?php

namespace common\controllers;

class UserController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
