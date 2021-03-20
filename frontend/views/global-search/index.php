<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\WebLang;

$this->params['breadcrumbs'][] = ['label' => WebLang::t('search') . WebLang::sp() . WebLang::t('result') . " - " . $searchText];
?>
<div class="container">
    <div class="col-lg-4 col-md-6 col-sm-12">
        <small><?= WebLang::t('first_20_items') ?></small>

        <?php $form = ActiveForm::begin(['id' => 'global-search', 'action' => Url::to(['global-search/index'])]); ?>
        <div class="gswrapper">
            <input name="s" id="s" type="text" class="gsfi" value="<?= $searchText ?>" style="width: 100%">
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <div class="clearfix"></div>

    <div class="m-t-30">

        <?php if (!$people && !$plans && !$crm && !$dms) { ?>
            <h2 class='lead'><?= WebLang::t('no_data') ?></h2>
        <?php } else { ?>
            <?php $activeFound = FALSE ?>
            <div role="tabpanel">
                <ul class="tab-nav" role="tablist">
                    <?php if (count($people) > 0) { ?>
                        <li class="<?= $activeFound ? "" : "active" ?>"><a href="#tab-people" role="tab" data-toggle="tab"><?= WebLang::t('people') ?></a></li>
                        <?php $activeFound = TRUE ?>
                    <?php } ?>
                    <?php if (count($plans) > 0) { ?>
                        <li class="<?= $activeFound ? "" : "active" ?>"><a href="#tab-plan" role="tab" data-toggle="tab"><?= WebLang::t('plan') ?></a></li>
                        <?php $activeFound = TRUE ?>
                    <?php } ?>
                    <?php if (count($crm) > 0) { ?>
                        <li class="<?= $activeFound ? "" : "active" ?>"><a href="#tab-crm" role="tab" data-toggle="tab"><?= WebLang::t('crm') ?></a></li>
                        <?php $activeFound = TRUE ?>
                    <?php } ?>
                    <?php if (count($dms) > 0) { ?>
                        <li class="<?= $activeFound ? "" : "active" ?>"><a href="#tab-document" role="tab" data-toggle="tab"><?= WebLang::t('document') ?></a></li>
                        <?php $activeFound = TRUE ?>
                    <?php } ?>
                </ul>

                <div class="tab-content">
                    <?php $activeTabPaneFound = FALSE ?>
                    <?php if (count($people) > 0) { ?>
                        <div role="tabpanel" class="tab-pane <?= $activeTabPaneFound ? "" : "active" ?>" id="tab-people">
                            <?php $activeTabPaneFound = TRUE ?>
                            <?= renderTab($people, $searchText) ?>
                        </div>
                    <?php } ?>
                    <?php if (count($plans) > 0) { ?>
                        <div role="tabpanel" class="tab-pane <?= $activeTabPaneFound ? "" : "active" ?>" id="tab-plan">
                            <?php $activeTabPaneFound = TRUE ?>
                            <?= renderTab($plans, $searchText) ?>
                        </div>
                    <?php } ?>
                    <?php if (count($crm) > 0) { ?>
                        <div role="tabpanel" class="tab-pane <?= $activeTabPaneFound ? "" : "active" ?>" id="tab-crm">
                            <?php $activeTabPaneFound = TRUE ?>
                            <?= renderTab($crm, $searchText) ?>
                        </div>
                    <?php } ?>
                    <?php if (count($dms) > 0) { ?>
                        <div role="tabpanel" class="tab-pane <?= $activeTabPaneFound ? "" : "active" ?>" id="tab-document">
                            <?php $activeTabPaneFound = TRUE ?>
                            <?= renderTab($dms, $searchText) ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php

function renderTab($items, $searchText) {

    $matchCount = 0;

    if (count($items) > 0) {
        ?>
        <table class="table table-condensed table-striped table-hover">
            <?php foreach ($items as $item) { ?>
                <tr href="<?= $item['resultUrl'] ?>" class="pointer">
                    <?php
                    $matchCount++;
                    $category = str_replace(trim($searchText), "<span class='text-danger'>" . trim($searchText) . "</span>", $item['category']);

                    $str = trim($item['resultDesc']);
                    if (!empty(trim($item['resultDesc']))) {
                        $str = str_replace(trim($searchText), "<span class='text-danger'>" . trim($searchText) . "</span>", $str);
                    }
//                    $str .= "<br>";
                    ?>

                    <td><?= $matchCount ?></td>
                    <td><?= trim($category) ?></td>
                    <td><?= $str ?></td>
                </tr>
            <?php } ?>
        </table>
        <?php
    } else {
        echo "<h2 class='lead'>" . WebLang::t('no_data') . "</h2>";
    }
}
