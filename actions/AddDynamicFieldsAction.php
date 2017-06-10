<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\events\DynamicChildrenAfterDataLoadEvent;

class AddDynamicFieldsAction extends LoadDynamicChildrenAction
{


    public function events() {
        return [
            DynamicChildrenProcessor::AFTER_LOAD_CHILD_MODELS_EVENT => [
                DynamicChildrenProcessor::class => [
                    [$this, 'transform']
                ],
            ]
        ];
    }


    public function loadChildModelsFromRequest() {
        foreach($this->fields as &$fields) {
            $fields->loadChildModelsFromRequest();
            $fields->afterLoadChildModels();
        }
    }


    public function transform(DynamicChildrenAfterDataLoadEvent $event) {
        $processor = $event->field_processor;
        $new_class = $processor->child_models_parent_class;
        $event->field_processor->child_models[] = new $new_class;
        $event->field_processor->child_models = array_slice($event->field_processor->child_models, -1, 1, true);
    }


    public function run() {
        $this->loadChildModelsFromRequest();
        return $this->render();
    }

}