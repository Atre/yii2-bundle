<?php

namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\widgets\PartialActiveForm;

class LoadDynamicFieldsAction extends DynamicFieldsAction
{

    public function getModel($id = null) {
        $this->model_class = $this->getModelClass();
        return $this->controller->findModel($this->model_class, $id, null, false);
    }


    public function run($id = null) {
        parent::run($id);
        $this->findModels();
        $this->initModels();
        $sub_model_parent_class = $this->sub_model_parent_class;
        $fields_html =
            $this->controller->renderAjax($sub_model_parent_class::getSubTableViewByCategory($this->category), [
                'models' => $this->sub_models,
            ]);
        return $this->controller->asJson(['html' => $fields_html,
                                          'fields' => PartialActiveForm::getAttributes()]);
    }
}