<?php
namespace dezmont765\yii2bundle\actions;

use yii\base\Event;

class AddDynamicFieldsAction extends LoadDynamicChildrenAction
{

    public function init() {
        parent::init();
        $self = $this;
        Event::on(ReverseFlowDynamicChildrenProcessor::className(),
                  ReverseFlowDynamicChildrenProcessor::AFTER_LOAD_CHILD_MODELS_EVENT,
            function (Event $event) use ($self) {
                $processor = $event->field_processor;
                $new_class = $processor->child_models_sub_class;
                $self->sliceOne($new_class, $processor->child_models);
            });
        Event::on(DirectFlowDynamicChildrenProcessor::className(),
                  DirectFlowDynamicChildrenProcessor::AFTER_LOAD_CHILD_MODELS_EVENT,
            function (Event $event) use ($self) {
                $processor = $event->field_processor;
                $new_class = $processor->child_models_parent_class;
                $self->sliceOne($new_class, $processor->child_models);
            });
    }

    public function loadChildModelsFromRequest() {
        foreach($this->fields as &$fields) {
            $fields->loadChildModelsFromRequest();
            $fields->afterLoadChildModels();
        }
    }

    public function sliceOne($class, &$models_array) {
        $models_array[] = new $class;
        $models_array = array_slice($models_array, -1, 1, true);
    }


    public function run($id = null) {
        $this->model = $this->getModel($id);
        $this->loadChildModelsFromRequest();
        return $this->render();
    }

}