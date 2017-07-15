<?php
/**
 * @var $this MainView
 * @var $widget \dezmont765\yii2bundle\widgets\alert\AlertWidget
 */
use dezmont765\yii2bundle\views\MainView;

$widget = $this->context;
?>
<div class="alert alert-<?= $general_color ?> in fade">
    <?= Yii::t('app', $general_message) ?>
    <a class="details-link">Details</a>
    <? if(count($success_alerts)) : ?>
        <pre style="display: none">
        <? var_dump($success_alerts); ?>
    </pre>
    <? endif ?>
    <? if(count($warning_alerts)) : ?>
        <pre style="display: none">
        <? var_dump($warning_alerts); ?>
    </pre>
    <? endif ?>
    <? if(count($error_alerts)) : ?>
        <pre style="display: none">
        <? var_dump($error_alerts); ?>
    </pre>
    <? endif ?>
    <a class="hide-link" style="display:none">Hide...</a>
</div>
<script>
    $('.details-link').click(function () {
        $(this).attr('style', 'display:none');
        $('.hide-link').attr('style', 'display:inline');
        $(this).siblings('pre').attr('style', 'display:block');
    });
    $('.hide-link').click(function () {
        $(this).attr('style', 'display:none');
        $('.details-link').attr('style', 'display:inline');
        $(this).siblings('pre').attr('style', 'display:none');
    });
    $(document).load(function () {
        $("html,body").stop().animate({
            scrollTop: $("html").offset().top
        }, 'fast');
    });

</script>

