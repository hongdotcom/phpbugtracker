<?php

use common\helpers\AifinHelper;
use yii\helpers\Url;
use richardfan\widget\JSRegister;
use common\models\WebLang;
use yii\widgets\Pjax;

$this->params['breadcrumbs'][] = 'Calendar';
$user = Yii::$app->user->getId('user');
$currPro = Yii::$app->request->get('pro');
$idclients = AifinHelper::getIdclients($user);
frontend\assets\DropZoneAsset::register($this);

$fullDayExists = FALSE;

$ajaxUrl = Url::to(['calendar/index']);
?>
<script>
    var ajaxUrl = "<?= $ajaxUrl ?>";
    var baseUrl = "<?= Url::to(['calendar/index']) ?>";
</script>

<link href="/css/calendar_full.css?v=<?= AifinHelper::genRandomKey() ?>" rel="stylesheet">
<!--<link href="/themes/aifin/css/calendar_compact.css" rel="stylesheet">-->

<!--<span class="text-primary f-20 pull-left"><?= WebLang::t('next_3_months') ?></span>-->
<div class="pull-right">
    <input type="checkbox" id="refresh"> <span id="refresh-label"></span>
</div>

<div class="col-md-8 col-sm-12">
    <?php Pjax::begin(['id' => 'calendar-calendar']) ?>
    <div class="tiva-events-calendar full" data-source="json"></div>
    <?php Pjax::end() ?>
</div>

<!-- timeslots -->
<div class="col-md-4 col-sm-12">
    <h2 class="lead text-primary"><?= WebLang::t('schedule') ?> <?= $today ?></h2>

    <?php if ($msg) { ?>
        <div class="alert alert-danger"><?= $msg ?></div>
    <?php } ?>
    <div class="clearfix"></div>

    <table class="table table-condensed">
        <tr>
            <th class="text-center"><?= WebLang::t('timeslot') ?></th>
            <th class="text-center"><?= WebLang::t('busy') ?></th>
            <th class="text-center"><?= WebLang::t('you') ?></th>
        </tr>
        <?php foreach ($timeslots as $timeslot) { ?>
            <tr>
                <th class="text-center"><?= $timeslot->full_day == 1 ? "<span class='label label-primary'>{$timeslot->value_eng}</label>" : $timeslot->value_eng ?></th>
                <td class="text-center">
                    <?php $selfFree = NULL ?>
                    <?php foreach ((array) $freebusys as $freebusy) { ?>
                        <?php
                        if ($freebusy->timeSlot->full_day == 1) {
                            $fullDayExists = TRUE;
                        }
                        ?>
                        <?php if ($freebusy->idtimeslot == $timeslot->id) { ?>
                            <?php
                            if ($freebusy->created_by == Yii::$app->user->identity->id) {
                                if ($freebusy->freebusy == common\models\FreeBusy::FREE) {
                                    $selfFree = TRUE;
                                } else {
                                    $selfFree = FALSE;
                                }
                            }
                            ?>
                            <label class='label label-info'><?= isset($selfFree) ? "You" : $freebusy->createdBy->name() ?></label>
                        <?php } ?>
                    <?php } ?>
                </td>
                <td class="text-center">
                    <?php if ($selfFree !== TRUE && $selfFree !== NULL) { ?>
                        <a class="btn btn-xs btn-success pointer" href="<?= Url::to(['calendar/index', 'idtimeslot' => $timeslot->id, 'action' => 1, 'date' => $today]) ?>"
                           >Set free</a>
                       <?php } else { ?>
                           <?php if (!$fullDayExists) { ?>
                            <a class="btn btn-xs btn-danger pointer m-l-15" href="<?= Url::to(['calendar/index', 'idtimeslot' => $timeslot->id, 'action' => 0, 'date' => $today]) ?>"
                               >Set busy</a>
                           <?php } ?>
                       <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<div class="clearfix"></div>

<div class="padding">
    <div class="progress">
        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="30" style="width: 0;">
            <span class="sr-only">0%</span>
        </div>
    </div>
</div>
<?php $this->registerJsFile(Yii::$app->request->baseUrl . '/js/calendar.js?v=' . AifinHelper::genRandomKey(), ['depends' => [\yii\web\JqueryAsset::className()]]); ?>
<script src="/js/tiva.js" id="7v9unltgzi85v5clohp95z2500w3ozvy">
</script>

<?php
JSRegister::begin([
    'position' => \yii\web\View::POS_END
]);
?>
<script>
    url = '<?= \yii\helpers\Url::to(['calendar/index']) ?>';

    // Path of events source
    var events_json = "<?= Url::to(['/calendar/datafeed']) ?>?month=<?= date("m") ?>";

        // Day, Month
        var wordMonth = new Array("Jan", "Feb", "Mar", "Apr",
                "May", "Jun", "Jul", "Aug", "Sep",
                "Oct", "Nov", "Dec");
        var wordDay_sun = "Sun";
        var wordDay_mon = "Mon";
        var wordDay_tue = "Tue";
        var wordDay_wed = "Wed";
        var wordDay_thu = "Thu";
        var wordDay_fri = "Fri";
        var wordDay_sat = "Sat";

        // View Button
        var calendar_view = "Calendar";
        var list_view = "Listing";
        var back = "Back";

        // Calendar Button
        var prev_year = "Last year";
        var prev_month = "Last month";
        var next_month = "Next month";
        var next_year = "Next year";

        var interval;
        var refresh_seconds = 30;
        var value = 0; // progress

        function reloadCalendar() {
            $('.calendar-event-name').remove();
            tiva_events = [];

            jQuery.getJSON(events_json, function (data) {
                for (var i = 0; i < data.items.length; i++) {
                    var event_date = new Date(data.items[i].year, Number(data.items[i].month) - 1, data.items[i].day);
                    data.items[i].date = event_date.getTime();
                    tiva_events.push(data.items[i]);
                }

                // Sort events by date
                tiva_events.sort(sortEventsByDate);

                for (var j = 0; j < tiva_events.length; j++) {
                    tiva_events[j].id = j;
                    if (!tiva_events[j].duration) {
                        tiva_events[j].duration = 1;
                    }
                }

                // Create calendar
                changedate('current', 'full');
                changedate('current', 'compact');

                jQuery('.tiva-events-calendar').each(function (index) {
                    // Initial view
                    var initial_view = (typeof jQuery(this).attr('data-view') != "undefined") ? jQuery(this).attr('data-view') : 'calendar';
                    if (initial_view == 'list') {
                        jQuery(this).find('.list-view').click();
                    }
                });
            });
            value = 0;
        }
        function processFreeBusy(action) {

            idcalendar = $('#modalEventView input[name=idcalendar]').val();
            remark = $('#modalEventView textarea[name=remark]').val();
            event_date = $('#modalEventView input[name=event_date]').val();
            idtimeslot = $('#modalEventView select[name=idtimeslot]').val();
            instructions = $('#modalEventView [name=instructions]').html();

            if (!idcalendar) {
                return false;
            }
            $.ajax({
                url: '<?= Url::to(['/calendar/process']) ?>?id=' + idcalendar,
                cache: false,
                dataType: 'json',
                type: 'POST',
                data: {
                    idcalendar: idcalendar,
                    action: action,
                    remark: remark,
                    event_date: event_date,
                    idtimeslot: idtimeslot,
                    instructions: instructions,
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    swal(t.failure, t.ajax_error, "error");
                },
                success: function (response) {
                    if (response.success) {
                        jQuery('#modalEventView').modal('hide');
                        swal({
                            title: t.success,
                            text: '',
                            type: "success",
                            allowOutsideClick: true
                        },
                                function () {
                                    reloadCalendar();
                                }
                        );
                    } else {
                        swalError(response.msg);
                    }
                }
            });
        }

        var timeoutId;

        function barAnim() {
            value += (100 / refresh_seconds);
            $(".progress-bar").css("width", value + "%").attr("aria-valuenow", value);

            if (value >= (100 + 100 / refresh_seconds)) {
                return true;
            } else {
                timeoutId = setTimeout(barAnim, 1000);
            }
        }

        function stopProgress() {
            clearInterval(interval);
            value = 0;
            $(".progress-bar").css("width", value + "%").attr("aria-valuenow", value);
            clearTimeout(timeoutId);
        }

        $(document).ready(function () {
            $('#refresh-label').html("Refresh every " + refresh_seconds + " seconds");
            $('#refresh').click(function () {
                if ($(this).is(':checked')) {
                    timeoutId = setTimeout(barAnim, 1000);
                    interval = setInterval(function () {
                        reloadCalendar();
                    }, refresh_seconds * 1000);
                } else {
                    stopProgress();
                }
            });
        });

</script>
<?php JSRegister::end(); ?>

