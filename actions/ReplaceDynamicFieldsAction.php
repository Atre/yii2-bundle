<?php

namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\widgets\PartialActiveForm;

class ReplaceDynamicFieldsAction extends LoadSingleDynamicFieldsAction
{
    private $key = null;


    public function init() {
        parent::init();
        $this->key = \Yii::$app->request->getBodyParam('key');
    }


    public function initModels() {
        if($this->child_models_sub_class !== null) {
            $sub_model_class = $this->child_models_sub_class;
            $this->loadModelsFromRequest($this->child_models, $sub_model_class);
            $this->child_models = [$this->key => $this->child_models[$this->key]];
        }
    }


    public function run($id = null) {
        $this->model = $this->getModel($id);
//        $this->findModels();
        $this->initModels();
        return $this->render();
    }


    public function render() {
        $sub_model_parent_class = $this->child_models_parent_class;
        $form = PartialActiveForm::begin();
        $fields_html =
            $this->controller->renderAjax($sub_model_parent_class::getSubTableViewByCategory($this->category), [
                'model' => $this->child_models[$this->key],
                'form' => $form,
                'key' => $this->key,
            ]);
        PartialActiveForm::end();
        return $this->controller->asJson(['html' => $fields_html,
                                          'fields' => PartialActiveForm::getAttributes()]);
    }

}