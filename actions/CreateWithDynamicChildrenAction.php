<?php

namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\actions\SingleDynamicFieldsAction;
use dezmont765\yii2bundle\widgets\PartialActiveForm;
use yii\bootstrap\Html;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 30.04.2017
 * Time: 15:57
 */
class CreateWithDynamicChildrenAction extends MultipleDynamicFieldsAction
{

    public function getModel($id = null) {
        $this->model_class = $this->getModelClass();
        $model_class = $this->model_class;
        return new $model_class;
    }


    public function run($id = null) {
        parent::run($id);
        $this->initModels();
        if(\Yii::$app->request->isAjax) {
            $result = [];
            PartialActiveForm::ajaxValidation($result, $this->model);
            foreach($this->fields as $field) {
                PartialActiveForm::ajaxValidationMultiple($result, $field[self::SUB_MODELS]);
            }
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        }
        $this->save();
        return $this->controller->render($this->getView(), ['model' => $this->model]);
    }


}