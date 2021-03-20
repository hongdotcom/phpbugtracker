<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['/crm-record/index']);
?>
Hello all,

User <?= $user->username ?> has just submitted a new ticket.
Subject: <?= $detail->subject ?>

Detail as follows: 
-----------------------------------------------------------------
<?= $detail->remark ?>

-----------------------------------------------------------------
If you want to review it, click the below link.

<?= $resetLink ?>


Have a good day! Thank you!

aiFin


Code A La Carte Limited
Units 1205-1208, Cyberport2, No. 100 Cyberport Road, Hong Kong
Web:     www.calc.asia
Email:   cs@calc.asia