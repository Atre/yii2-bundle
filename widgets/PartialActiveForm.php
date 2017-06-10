<?php
namespace dezmont765\yii2bundle\widgets;

use dezmont765\yii2bundle\models\AExtendableActiveRecord;
use dezmont765\yii2bundle\models\MainActiveRecord;
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


    public static function ajaxValidationMultiple(&$result, $models, $attributes = null) {
        /* @var $model Model */
        foreach($models as $key => $model) {
            self::ajaxValidation($result, $model, $attributes, $key);
            if($model instanceof AExtendableActiveRecord) {
                 self::ajaxValidation($result, $model, $attributes, $key);
            }
        }
        return $result;
    }


    /**
     * @param $result
     * @param Model $model
     * @param $attributes
     * @param bool $key
     */
    public static function ajaxValidation(&$result, $model, $attributes = null, $key = null) {
        $model->validate($attributes);
        foreach($model->getErrors() as $attribute => $errors) {
            if($key !== null) {
                $result[Html::getInputId($model, "[$key]" . $attribute)] = $errors;
            }
            else {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }
        }
    }

}