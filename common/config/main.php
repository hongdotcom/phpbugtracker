<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'bootstrap' => ['log'],
    'modules' => [''],
//    'bootstrap' => ['log','encrypter'],
//    'modules' => ['encrypter'],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
//        'encrypter' => [
//            'class' => '\nickcv\encrypter\components\Encrypter',
//            'globalPassword' => 'aifinasia',
//            'iv' => '0234567890123456',
//            'useBase64Encoding' => true,
//            'use256BitesEncoding' => false,
//        ],
    ],
    'name' => 'Technical Support System',
//    'timezone' => 'Asia/Hong_Kong',
];
