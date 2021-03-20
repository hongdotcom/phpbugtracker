<?php

use yii\helpers\Html;

//common\assets\DropZoneAsset::register($this);
/* @var $this yii\web\View */
/* @var $model common\models\CrmRecord */

$this->title = 'Create';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-sm-12">

    <?php if ($errors = $model->getErrors()) { ?>
        <ul>
            <?php foreach ($errors as $error) { ?>
                <li><?= var_dump($error) ?></li>
            <?php } ?>
        <?php } ?>
    </ul>
    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
