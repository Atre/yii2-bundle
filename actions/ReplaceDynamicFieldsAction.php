<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\events\DynamicChildrenAfterDataLoadEvent;
use dezmont765\yii2bundle\widgets\PartialActiveForm;
use yii\base\Event;

class ReplaceDynamicFieldsAction extends LoadDynamicChildrenAction
{
    private $key = null;


    public function init() {
        parent::init();
        $this->key = \Yii::$app->request->getBodyParam('key');
    }
    public function events() {
        return [
            DynamicChildrenProcessor::AFTER_LOAD_CHILD_MODELS_EVENT => [
                DynamicChildrenProcessor::class => [
                    [$this, 'transform']
                ],
            ]
        ];
    }

    public function transform(DynamicChildrenAfterDataLoadEvent $event) {
        $event->field_processor->child_models = [$this->key => $event->field_processor->child_models[$this->key]];
    }


    public function run() {
        $this->loadChildModelsFromRequest();
        return $this->render();
    }

    public function loadChildModelsFromRequest() {
        foreach($this->fields as &$fields) {
            $fields->loadChildModelsFromRequest();
            $fields->afterLoadChildModels();
        }
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