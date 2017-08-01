<?php
namespace dezmont765\yii2bundle\widgets;

use yii\widgets\Block;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 18.07.2017
 * Time: 19:29
 */
class AutoRegistrableScriptBlock extends Block
{
    public $renderInPlace = true;


    public function run() {
        $this->renderInPlace = true;
        $result = parent::run();
        \Yii::$app->view->registerJs($result);
    }
}