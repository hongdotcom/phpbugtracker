<?php

use yii\helpers\Url;
use common\models\WebLang;
use common\helpers\IfapHelper;
use yii\widgets\ActiveForm;

$lang = Yii::$app->language;
?>
<div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-12 reading">
    <div class="pull-right">
        <div class="gswrapper">
            <?php $form = ActiveForm::begin(['id' => 'kb-search', 'action' => Url::to(['knowledge/search'])]); ?>
            <input id="autocomplete" class="gsfi w-100" autocomplete="off" name="s" placeholder="<?= WebLang::t('search') ?>" value="<?= $searchText ?>">
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php if (count($items) > 0) { ?>
        <div class="list-group">
            <div class="lg-header">
                <h2>"<?= $searchText ?>" <?= WebLang::t('search') . WebLang::sp() . WebLang::t('result') ?><br>
                    <small><?= WebLang::t('first_20_items') ?></small>
                </h2>
            </div>
            <div class="lg-body">
                <?php foreach ($items as $item) { ?>
                    <a class="list-group-item media" href="<?= Url::to(['knowledge/article']) . "?id=" . $item['id'] ?>">
                        <div class="media-body">
                            <div class="pull-right"><?= IfapHelper::icon('text-info', IfapHelper::SIGN_GOTO) ?> <?= $item['views'] ?> <?= WebLang::t('views') ?></div>
                            <div class="lgi-heading">
                                <?= $item['subject'] ?>
                            </div>
                            <span class="lgi-text"><label class="label label-info"><?= $item['bbsBoard'][$lang] ?></label> <?= $item['created_at'] ?></span>
                        </div>
                    </a>
                <?php } ?>
            </div>
        </div>
    <?php } else { ?>
        <h2 class='lead'><?= WebLang::t('no_matching_record') ?></h2>
    <?php } ?>
</div>
