<?php

/**
 * Shortcut function to \yii\helpers\Html::encode($var)
 * @param string $var
 * @return string
 */
function e($var) {
    return \yii\helpers\Html::encode($var);
}

function ebr($var) {
    return nl2br(\yii\helpers\Html::encode($var));
}

function dd($array = null) {
    if (YII_DEBUG) {
        $str = debug_backtrace(1, 1)[0]['file'];
        $str .= " line " . debug_backtrace(1, 1)[0]['line'];
        $str .= PHP_EOL;
        echo $str;
        var_dump($array);
//        Yii::$app->end();
        exit();
    }
}

/**
 * Dump single ActiveModel attributes
 * @param ActiveModel $data
 */
function da($data, $die = TRUE) {
    if (YII_DEBUG) {
        var_dump($data->attributes);
    }
    if ($die) {
        die();
    }
}

/**
 * Dump multiple ActiveModels attributes
 * @param ActiveModels $data
 */
function das($data) {
    if (YII_DEBUG) {
        $str = debug_backtrace(1, 1)[0]['file'];
        $str .= " line " . debug_backtrace(1, 1)[0]['line'];
        $str .= PHP_EOL;
        echo $str;
        foreach ($data as $key => $item) {
            if (isset($item->attributes)) {
                var_dump($item->attributes);
            } else {
                echo $key;
                var_dump($item);
            }
        }
        die();
    }
}

function dd1($data) {
    if (YII_DEBUG) {

        echo debug_backtrace(1, 1)[0]['file'];
        echo " line " . debug_backtrace(1, 1)[0]['line'];
        echo PHP_EOL;

        if (isset($data->attributes)) {
            var_dump($data->attributes);
        } else {
            if (isset($data[1])) {
                echo "<pre>" . count($data) . " rows in model</pre>";
                foreach ((array) $data as $i => $item)
                    if (is_array($item)) {
                        var_dump($item);
                    } else {
                        yii\helpers\VarDumper::dump($item->attributes, 10, true);
                    }
            } else {
                var_dump($data);
            }
        }
        Yii::$app->end();
    }
}

function dd2($data) {
    yii\helpers\VarDumper::dump($data, 10, true);
    Yii::$app->end();
}

function d() {
    array_map(function ($x) {
        echo \yii\helpers\VarDumper::dump($x, 10, true);
    }, func_get_args());
}

function pre($mixed) {
    if (YII_DEBUG) {
        echo '<pre>';
        print_r($mixed);
        echo '</pre>';
        die();
    }
}
