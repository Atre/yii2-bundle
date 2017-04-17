<?php
namespace dezmont765\yii2bundle\widgets;

use Yii;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 15.02.2016
 * Time: 20:38
 */
class PartialActiveForm extends ActiveForm
{
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


    public static function validationMultiple($models, $attributes = null, array $model_without_id = []) {
        $result = [];
        $model_without_id = array_flip($model_without_id);
        /* @var $model Model */
        foreach($models as $i => $model) {
            $model->validate($attributes);
            foreach($model->getErrors() as $attribute => $errors) {
                if(isset($model_without_id[$model->className()])) {
                    $result[Html::getInputId($model, $attribute)] = $errors;
                }
                else {
                    if($model->isNewRecord) {
                        $id = $i;
                    }
                    else {
                        $id = $model->id;
                    }
                    $result[Html::getInputId($model, "[$id]" . $attribute)] = $errors;
                }
            }
        }
        return $result;
    }

}