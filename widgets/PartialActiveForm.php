<?php
namespace dezmont765\yii2bundle\widgets;
use Yii;
use yii\bootstrap\ActiveForm;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 15.02.2016
 * Time: 20:38
 */

class PartialActiveForm extends ActiveForm {
    const SESSION_CONTAINER = 'attributes_container';
    public $fieldClass = 'dezmont765\yii2bundle\widgets\PartialActiveField';
    public $enableAjaxValidation = true;
    public $enableClientValidation = false;
    public $enableClientScript = false;

    public function init() {

    }

    public function run() {
        Yii::$app->session[self::SESSION_CONTAINER] = $this->attributes;
    }

    public static function getAttributes() {
        return Yii::$app->session[self::SESSION_CONTAINER];
    }
}