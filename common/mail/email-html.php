<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\models\WebLang;

//use common\components\MiscHelpers;
//use frontend\models\Meeting;
//use frontend\models\UserContact;
/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?= $title ?></title>

        <style type="text/css">
            /* Take care of image borders and formatting, client hacks */
            img { max-width: 600px; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic;}
            a img { border: none; }
            table { border-collapse: collapse !important;}
            #outlook a { padding:0; }
            .ReadMsgBody { width: 100%; }
            .ExternalClass { width: 100%; }
            .backgroundTable { margin: 0 auto; padding: 0; width: 100% !important; }
            table td { border-collapse: collapse; }
            .ExternalClass * { line-height: 115%; }
            .container-for-gmail-android { min-width: 600px; }


            /* General styling */
            * {
                font-family: Helvetica, Arial, sans-serif;
            }

            body {
                -webkit-font-smoothing: antialiased;
                -webkit-text-size-adjust: none;
                width: 100% !important;
                margin: 0 !important;
                height: 100%;
                color: #676767;
            }
            .table {
                padding: 6px 10px !important;
            }
            .table-bordered {
                border: 1px #eee solid !important;
            }
            td {
                font-family: Helvetica, Arial, sans-serif;
                font-size: 14px;
                color: #777777;
                text-align: left;
                line-height: 21px;
                padding: 10px 15px;
            }
            th {
                padding: 10px 15px;
            }
            a {
                color: #676767;
                text-decoration: none !important;
            }

            .pull-left {
                text-align: left;
            }

            .pull-right {
                text-align: right;
            }

            .header-lg,
            .header-md,
            .header-sm {
                font-size: 24px;
                font-weight: 700;
                line-height: normal;
                padding: 35px 0 0;
                color: #4d4d4d;
            }

            .header-md {
                font-size: 24px;
            }

            .header-sm {
                padding: 5px 0;
                font-size: 18px;
                line-height: 1.3;
            }

            .content-padding {
                padding: 20px 0 30px;
            }

            .mobile-header-padding-right {
                width: 290px;
                text-align: right;
                padding-left: 10px;
            }

            .mobile-header-padding-left {
                width: 290px;
                text-align: left;
                padding-left: 10px;
                padding-top: 10px;
                padding-bottom: 20px;
            }

            .free-text {
                width: 100% !important;
                padding: 10px 60px 0px;
                text-align: left;
            }

            .block-rounded {
                border-radius: 5px;
                border: 1px solid #e5e5e5;
                vertical-align: top;
            }

            .button {
                padding: 30px 0;
            }

            .info-block {
                padding: 0 20px;
                width: 260px;
            }

            .block-rounded {
                width: 260px;
            }

            .info-img {
                width: 258px;
                border-radius: 5px 5px 0 0;
            }

            .force-width-img {
                width: 480px;
                height: 1px !important;
            }

            .force-width-full {
                width: 600px;
                height: 1px !important;
            }

            .force-width-gmail {
                min-width:600px;
                height: 0px !important;
                line-height: 1px !important;
                font-size: 1px !important;
            }

            .button-width {
                width: 228px;
            }
            .text-success {
                color: #67bd6a !important;
            }
            .text-info {
                color: #29B6F6 !important;
            }
            .text-danger {
                color: #f6675d !important;
            }
            .text-primary {
                color: #26A69A !important;
            }
            .text-warning {
                color: #ffa829 !important;
            }
        </style>

        <style type="text/css" media="screen">
            @import url('//fonts.googleapis.com/css?family=Oxygen:400,700');
        </style>

        <style type="text/css" media="screen">
            @media screen {
                /* Thanks Outlook 2013! */
                * {
                    font-family: 'Oxygen', 'Helvetica Neue', 'Arial', 'sans-serif' !important;
                }
            }
        </style>

        <style type="text/css" media="only screen and (max-width: 480px)">
            /* Mobile styles */
            @media only screen and (max-width: 480px) {

                table[class*="container-for-gmail-android"] {
                    min-width: 290px !important;
                    width: 100% !important;
                }

                table[class="w320"] {
                    width: 320px !important;
                }

                img[class="force-width-gmail"] {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                }


                a[class="button-width"],
                a[class="button-mobile"] {
                    width: 248px !important;
                }

                td[class*="mobile-header-padding-left"] {
                    width: 160px !important;
                    padding-left: 0 !important;
                }

                td[class*="mobile-header-padding-right"] {
                    width: 160px !important;
                    padding-right: 0 !important;
                }

                td[class="header-lg"] {
                    font-size: 24px !important;
                    padding-bottom: 5px !important;
                }

                td[class="header-md"] {
                    font-size: 18px !important;
                    padding-bottom: 5px !important;
                }

                td[class="content-padding"] {
                    padding: 5px 0 30px !important;
                }

                td[class="button"] {
                    padding: 5px !important;
                }

                td[class*="free-text"] {
                    padding: 10px 18px 30px !important;
                }

                img[class="force-width-img"],
                img[class="force-width-full"] {
                    display: none !important;
                }

                td[class="info-block"] {
                    display: block !important;
                    width: 280px !important;
                    padding-bottom: 40px !important;
                }

                td[class="info-img"],
                img[class="info-img"] {
                    width: 278px !important;
                }
            }
        </style>
    </head>

    <body bgcolor="#f7f7f7">
        <table align="center" cellpadding="4" cellspacing="4" class="container-for-gmail-android" width="100%">
            <tr>
                <td align="left" valign="top" width="100%">
                    <center>
                        <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#ffffff">
                            <tr>
                                <td width="100%" height="80" valign="top" style="text-align: center; vertical-align:middle;">
                                    <center>
                                        <table cellpadding="0" cellspacing="0" width="600" class="w320">
                                            <tr>
                                                <td class="pull-left mobile-header-padding-left" style="vertical-align: middle;">
                                                    <img height="60" src="<?= $logoUrl ?>" alt="logo" style="margin-right: 20px;">
                                                </td>
                                                <td class="pull-right mobile-header-padding-right" style="color: #4d4d4d;">
                                                    <?= $slogan ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </center>
                                    <!--[if gte mso 9]>
                                    </v:textbox>
                                  </v:rect>
                                  <![endif]-->
                                </td>
                            </tr>
                        </table>
                    </center>
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" width="100%" class="content-padding">
                    <center>
                        <table cellspacing="0" cellpadding="0" width="600" class="w320">
                            <tr>
                                <td class="header-lg">
                                    <?= $title ?>
                                </td>
                            </tr>
                            <?php if (isset($subContent)) { ?>
                                <tr>
                                    <td class="text-center header-sm">
                                        <?= $subContent ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td class="free-text">
                                    <?= nl2br($content) ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="button">
                                    <div style="text-align:center;">
                                        <a  class="button-mobile" href="<?= $buttonUrl ?>"
                                            style="background-color:#ff6f6f;border-radius:5px;color:#ffffff;display:inline-block;font-family:'Cabin', Helvetica, Arial, sans-serif;font-size:14px;font-weight:regular;line-height:45px;text-align:center;text-decoration:none;width:155px;-webkit-text-size-adjust:none;mso-hide:all;"
                                            ><?= $buttonText ?></a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </center>
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" width="100%" style="background-color: #f7f7f7; height: 100px;">
                    <center>
                        <table cellspacing="0" cellpadding="0" width="600" class="w320">
                            <tr>
                                <td align="center" style="text-align: center;">
                                    <span style="font-size: 28px; margin-top: 30px;"><?= $coName ?></span><br />
                                    <strong><?= $csName ?></strong><br />
                                    (<?= $csEmail ?>) <br /><br />
                                </td>
                            </tr>
                        </table>
                    </center>
                </td>
            </tr>
        </table>
    </body>
</html>