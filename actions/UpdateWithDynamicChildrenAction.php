<?php

namespace dezmont765\yii2bundle\actions;
use dezmont765\yii2bundle\widgets\PartialActiveForm;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 30.04.2017
 * Time: 21:22
 */
class UpdateWithDynamicChildrenAction extends MultipleDynamicFieldsAction
{


    public function getModel($id = null) {
        $this->model_class = $this->getModelClass();
        return $this->controller->findModel($this->model_class, $id);
    }


    public function run($id = null) {
        parent::run($id);
        $this->findExistingSubModels();
        $this->initModels();
        $this->save();
        return $this->controller->render($this->getView(), ['model' => $this->model]);
    }
}