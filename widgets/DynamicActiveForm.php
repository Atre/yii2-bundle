<?php

namespace dezmont765\yii2bundle\widgets;

use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 11.05.2017
 * Time: 13:01
 */
class DynamicActiveForm extends ActiveForm
{
    public function init() {
        parent::init();
        $this->validationUrl = Url::to(['ajax-validation']);
    }


    public $enableAjaxValidation = true;
    public $enableClientValidation = false;
}