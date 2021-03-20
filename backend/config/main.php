<?php

$params = array_merge(
        require __DIR__ . '/../../common/config/params.php', require __DIR__ . '/../../common/config/params-local.php', require __DIR__ . '/params.php', require __DIR__ . '/params-local.php'
);

use \yii\web\Request;

$baseUrl = str_replace('/web', '', (new Request)->getBaseUrl());

return [
    'id' => 'app-backend',
    'name' => 'Bugtracker Backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
//            'useMemcached' => true,
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
            'baseUrl' => $baseUrl,
            'csrfCookie' => [
                'httpOnly' => true,
//                'secure' => true
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            // Disable index.php
            'showScriptName' => false,
            // Disable r= routes
            'enablePrettyUrl' => true,
            'rules' => array(
                '' => 'site/index',
                '/' => 'site/index',
                '<alias:index|login|logout>' => 'site/<alias>',
//                '<controller:\w+>/<action:\w+>/<id:\w+>' => '<controller>/<action>',
//                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ],
//        'view' => [
//            'class' => 'yii\web\View',
//            'renderers' => [
//                'twig' => [
//                    'class' => 'yii\twig\ViewRenderer',
//                    'cachePath' => '@runtime/Twig/cache',
//                    // Array of twig options:
//                    'options' => [
//                        'auto_reload' => true,
//                    ],
//                    'globals' => ['html' => '\yii\helpers\Html'],
//                    'uses' => ['yii\bootstrap'],
//                ],
//            ],
//        ],
    ],
    'params' => $params,
];
