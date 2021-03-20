<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class SummernoteAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'summernote/dist/summernote.css'
    ];
    public $js = [
        'summernote/dist/summernote.min.js',
//        'themes/aifin/summernote/dist/summernote-cleaner.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
}