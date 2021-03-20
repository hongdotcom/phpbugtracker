<?php ?>
<style>
    td:nth-child(1) {
        white-space: nowrap;
        font-weight: 700;
    }
    td:nth-child(2) {
        white-space: pre-line;
    }
</style>    

<table class="table table-striped table-condensed table-sm">
    <?= $this->render("@frontend/views/aifin-gitlog"); ?>
</table>