<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'material-design-iconic-font/dist/css/material-design-iconic-font.min.css',
//        "malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css",
//        "animate.css/animate.min.css",
//        'bootstrap-select/dist/css/bootstrap-select.min.css',
//        'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
//        'font-awesome/css/font-awesome.min.css',
//        'ladda/ladda-themeless.min.css',
//        'select2/select2.min.css',
        'css/hongadd.css',
    ];
    public $js = [
//        'themes/aifin/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js',
        'bootstrap-growl/bootstrap-growl.min.js',
        'bootstrap-notify/bootstrap-notify.min.js',
//        'themes/aifin/bootstrap-select/dist/js/bootstrap-select.min.js',
//        'themes/aifin/jquery-validate/dist/jquery.validate.min.js',
//        'themes/aifin/moment/min/moment.min.js',
//        'themes/aifin/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
//        'themes/aifin/Waves/dist/waves.min.js',
//        'themes/aifin/js/jquery.pleaseWait.min.js',
//        'themes/aifin/js/jquery.loadJSON.js',
//        'themes/aifin/autosize/dist/autosize.min.js',
//        'js/docs.min.js',
//        'themes/aifin/ladda/spin.min.js',
//        'themes/aifin/ladda/ladda.min.js',
//        'themes/aifin/select2/select2.full.min.js',
//        'themes/aifin/ladda/ladda.jquery.min.js',
        'js/app.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
