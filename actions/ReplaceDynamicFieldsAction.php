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
        foreach($this->fields as &$fields) {
                $fields->loadModelsFromRequest();
                $fields->child_models = [$this->key => $fields->child_models[$this->key]];
        }
    }


    public function run($id = null) {
        $this->model = $this->getModel($id);
        $this->initModels();
        return $this->render();
    }


    public function render() {
        $fields_html = '';
        foreach($this->fields as &$fields) {
            $form = PartialActiveForm::begin();
            $sub_model_parent_class = $fields->child_models_parent_class;
            $fields_html =
                $this->controller->renderAjax($sub_model_parent_class::getSubTableViewByCategory($fields->category), [
                    'model' => $fields->child_models[$this->key],
                    'form' => $form,
                    'key' => $this->key,
                ]);
            PartialActiveForm::end();
        }
        return $this->controller->asJson(['html' => $fields_html,
                                          'fields' => PartialActiveForm::getAttributes()]);
    }

}