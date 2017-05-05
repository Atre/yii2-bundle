<?php

namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\widgets\PartialActiveForm;

class LoadDynamicFieldsAction extends DynamicFieldsAction
{

    public function getModel($id = null) {
        $this->model_class = $this->getModelClass();
        $model = $this->controller->findModel($this->model_class, $id, null, false);
        if(!$model instanceof $this->model_class) {
            return new $this->model_class;
        }
        else return $model;
    }


    public function run($id = null) {
        parent::run($id);
        $this->findModels();
        $this->initModels();
        return $this->render();
    }


    public function render() {
        $sub_model_parent_class = $this->sub_model_parent_class;
        $fields_html =
            $this->controller->renderAjax($sub_model_parent_class::subTableBaseView(), [
                'view' => $sub_model_parent_class::getSubTableViewByCategory($this->category),
                'models' => $this->sub_models,
            ]);
        return $this->controller->asJson(['html' => $fields_html,
                                          'fields' => PartialActiveForm::getAttributes()]);
    }
}