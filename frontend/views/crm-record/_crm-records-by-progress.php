<?php

use common\helpers\AifinHelper;
?>
<div class="col-sm-12">
    <div class="row">
        <div style='overflow-x: auto;'>
            <?php foreach ((array) $progresses as $progress) { ?>
                <?php $crmRecords = $progress->getCrmRecords($searchPhase, $idclient, $excludeCategories)->all(); ?>
                <?php if ($crmRecords) { ?>
                    <div class="progress-div">
                        <h4 class="text-primary"><?= $progress->name() ?></h4>
                        <ul class="list-group">
                            <?php foreach ((array) $crmRecords as $item) { ?>
                                <?php
                                if ($item->lastCrmActivity) {
                                    if ($item->lastCrmActivity->createdBy) {
                                        $displayName = $item->lastCrmActivity->createdBy->name();
                                    } else {
                                        $displayName = $item->createdBy->name();
                                    }
                                } else {
                                    $displayName = $item->createdBy->name();
                                }
                                ?>
                                <li>
                                    <a class="pointer list-group-item list-group-item-<?= $item->priority->cssColor() ?>" 
                                       onclick='toggleActivityModal("#activity-modal", <?= $item->id ?>)'>
                                        <span class="badge badge-default"><?= $item->taskCategory->name() ?></span>
                                        <?php if ($item->idclient <> 1) { ?>
                                            <span class="badge badge-warning"><?= $item->clientSite->name() ?></span>
                                        <?php } ?>

                                        <p><?= $item->subject ? $item->subject : "No subject provided" ?></p>
                                        <?php if ($lastCrmActivity = $item->lastCrmActivity) { ?>
                                            <small><?= nl2br(AifinHelper::xssafe($lastCrmActivity->internal_remark)) ?></small>
                                        <?php } else { ?>
                                            <small><?= nl2br(AifinHelper::xssafe($item->remark)) ?></small>
                                        <?php } ?>
                                        <div class='text-right'>
                                            <small>
                                                <?= $displayName ?> <?= AifinHelper::showXAgo($item->updated_at ? $item->updated_at : $item->created_at) ?>
                                                (#<?= $item->id ?>)
                                            </small>
                                        </div>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
