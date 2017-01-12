<?php
/**
 * @var $this AlertWidget
 */
?>
<div class="alert alert-<?= $this->general_color ?> in fade">
    <?= Yii::t('app', $this->general_message) ?>
    <a class="details-link">Details</a>
    <? if(count($this->success_alerts)) : ?>
        <pre style="display: none">
        <? var_dump($this->success_alerts); ?>
    </pre>
    <? endif ?>
    <? if(count($this->warning_alerts)) : ?>
        <pre style="display: none">
        <? var_dump($this->warning_alerts); ?>
    </pre>
    <? endif ?>
    <? if(count($this->error_alerts)) : ?>
        <pre style="display: none">
        <? var_dump($this->error_alerts); ?>
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

