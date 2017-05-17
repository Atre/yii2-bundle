<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\widgets\PartialActiveForm;
use yii\base\Event;

class ReplaceDynamicFieldsAction extends LoadDynamicChildrenAction
{
    private $key = null;


    public function init() {
        parent::init();
        $self = $this;
        $this->key = \Yii::$app->request->getBodyParam('key');
        Event::on(ReverseFlowDynamicChildrenProcessor::className(),
                  ReverseFlowDynamicChildrenProcessor::AFTER_LOAD_CHILD_MODELS_EVENT,
            function (Event $event) use ($self) {
                $processor = $event->field_processor;
                $self->transform($processor->child_models);
            });
        Event::on(DirectFlowDynamicChildrenProcessor::className(),
                  DirectFlowDynamicChildrenProcessor::AFTER_LOAD_CHILD_MODELS_EVENT,
            function (Event $event) use ($self) {
                $processor = $event->field_processor;
                $self->transform($processor->child_models);
            });
    }


    public function transform(&$models) {
        $models = [$this->key => $models[$this->key]];
    }


    public function run($id = null) {
        $this->model = $this->getModel($id);
        $this->loadChildModelsFromRequest();
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