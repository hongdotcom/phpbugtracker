<?php

$this->title = 'Bugtracker Home';
?>

<div class="site-index">

    <div class="body-content">

        <h2>Backend Actions</h2>
        <?php foreach ($actionList as $controller => $items) { ?>
            <h4 class="text-info"><?= $controller ?></h4>
            <p>Methods</p>
            <ul>
                <?php foreach ($items as $i => $item) { ?>
                    <li><?= $item ?></li>
                <?php } ?>
            </ul>
        <?php } ?>

    </div>
</div>
