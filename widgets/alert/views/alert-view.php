<?php
use dezmont765\yii2bundle\components\Alert;

/**
 * @var $this \app\components\MainView
 * @var $widget \dezmont765\yii2bundle\widgets\alert\AlertWidget
 */
$widget = $this->context;
?>
<div class="alert alert-<?= $general_color ?> alert-dismissible" style="margin-top: 20px">

    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
    </button>
    <? if(count($success_alerts)) : ?>
        <?= Alert::recursiveFind($success_alerts, 'msg'); ?>
    <? endif ?>
    <? if(count($warning_alerts)) : ?>
        <?= Alert::recursiveFind($warning_alerts, 'msg'); ?>
    <? endif ?>
    <? if(count($error_alerts)) : ?>
        <?= Alert::recursiveFind($error_alerts, 'msg'); ?>
    <? endif ?>
</div>
<script>


</script>

