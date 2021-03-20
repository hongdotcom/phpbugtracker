<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for DropZone Widget
 */
class DropZoneAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
//    public $sourcePath = '@dropzone/bower_components';

    public $js = [
        "js/dropzone52/dropzone.js"
    ];
    public $css = [
        "js/dropzone52/dropzone.css"
    ];

    /**
     * @var array
     */
    public $publishOptions = [
        'forceCopy' => true
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}

