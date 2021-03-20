<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use yii\helpers\Json;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="skin-blue-light sidebar-mini">
        <?php $this->beginBody() ?>
        <script>
            var t = <?= Json::encode($this->params['t']) ?>;
            var apiBase = <?= Json::encode(Url::to(['/'])) ?>;
            var baseUrl = <?= Json::encode(Url::to(['/'])) ?>;
        </script>

        <div class="wrapper">
            <?php
            NavBar::begin([
                'brandLabel' => Yii::$app->name,
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-calc navbar-fixed-top',
                ],
            ]);

            if (Yii::$app->user->isGuest) {
                //$menuItems[] = ['label' => 'Signup', 'url' => ['/site/signup']];
                $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
            } else {
                $menuItems = [
                    ['label' => 'Home', 'url' => ['/site/index']],
                    ['label' => 'Open Tickets', 'url' => ['/crm-record/index']],
                    ['label' => 'Closed Tickets', 'url' => ['/crm-record/index', 'closed' => 1]],
                    ['label' => 'Aifin Changelog', 'url' => ['/aifin/index']],
                ];
                $menuItems[] = '<li>'
                        . Html::beginForm(['/site/logout'], 'post')
                        . Html::submitButton(
                                'Logout (' . Yii::$app->user->identity->username . ')', ['class' => 'btn btn-link logout']
                        )
                        . Html::endForm()
                        . '</li>';
            }
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuItems,
            ]);
            NavBar::end();
            ?>

            <div class="container">
                <div style="margin-top: 70px;">
                    <?=
                    Breadcrumbs::widget([
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                    ])
                    ?>
                    <div class="col-sm-12">
                        <?= Alert::widget() ?>
                    </div>
                    <?= $content ?>
                </div>
            </div>
        </div>

        <footer class="footer m-t-25">
            <div class="container">
                <p class="pull-left">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
            </div>
        </footer>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
