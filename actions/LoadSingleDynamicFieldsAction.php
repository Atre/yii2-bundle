<?php

namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\widgets\PartialActiveForm;

class LoadSingleDynamicFieldsAction extends SingleDynamicFieldsAction
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
        $this->findExistingSubModels();
        $this->initModels();
        return $this->render();
    }


    public function render() {
        $child_models_parent_class = $this->child_models_parent_class;
        $fields_html =
            $this->controller->renderAjax($child_models_parent_class::subTablesBaseView(), [
                'view' => $child_models_parent_class::getSubTableViewByCategory($this->category),
                'models' => $this->child_models,
            ]);
        return $this->controller->asJson(['html' => $fields_html,
                                          'fields' => PartialActiveForm::getAttributes()]);
    }
}