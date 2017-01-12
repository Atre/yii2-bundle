<?php use app\components\Alert;

/** @var $this AlertWidget */
?>
<div class="alert alert-<?= $this->general_color ?> alert-dismissible" style="margin-top: 20px">

    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
    </button>
    <? if(count($this->success_alerts)) : ?>
        <?= Alert::recursiveFind($this->success_alerts, 'msg'); ?>
    <? endif ?>
    <? if(count($this->warning_alerts)) : ?>
        <?= Alert::recursiveFind($this->warning_alerts, 'msg'); ?>
    <? endif ?>
    <? if(count($this->error_alerts)) : ?>
        <?= Alert::recursiveFind($this->error_alerts, 'msg'); ?>
    <? endif ?>
</div>
<script>


</script>

