<?php

namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\widgets\PartialActiveForm;

class ReplaceDynamicFieldsAction extends LoadDynamicFieldsAction
{
    private $key = null;


    public function init() {
        parent::init();
        $this->key = \Yii::$app->request->getBodyParam('key');
    }


    public function initModels() {
        if($this->sub_model_class !== null) {
            $sub_model_class = $this->sub_model_class;
            $this->loadModelsFromRequest($this->sub_models, $sub_model_class);
            $this->sub_models = [$this->key => $this->sub_models[$this->key]];
        }
    }


    public function render() {
        $sub_model_parent_class = $this->sub_model_parent_class;
        $form = PartialActiveForm::begin();
        $fields_html =
            $this->controller->renderAjax($sub_model_parent_class::getSubTableViewByCategory($this->category), [
                'model' => $this->sub_models[$this->key],
                'form' => $form,
                'key' => $this->key,
            ]);
        PartialActiveForm::end();
        return $this->controller->asJson(['html' => $fields_html,
                                          'fields' => PartialActiveForm::getAttributes()]);
    }

}