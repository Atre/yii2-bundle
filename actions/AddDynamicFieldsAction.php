<?php
namespace dezmont765\yii2bundle\actions;
class AddDynamicFieldsAction extends LoadDynamicFieldsAction
{
    public function initModels() {
        foreach($this->fields as &$fields) {
            $child_models_sub_class = $fields->child_models_sub_class;
            if($child_models_sub_class !== null) {
                $fields->loadModelsFromRequest();
                $fields->child_models[] = new $child_models_sub_class;
                $fields->child_models = array_slice($fields->child_models, -1, 1, true);
            }
        }
    }


    public function run($id = null) {
        $this->model = $this->getModel($id);
        $this->initModels();
        return $this->render();
    }

}