<?php

namespace dezmont765\yii2bundle\actions;
class AddDynamicFieldsAction extends LoadSingleDynamicFieldsAction
{
    public function initModels() {
        if($this->child_models_sub_class !== null) {
            $sub_model_class = $this->child_models_sub_class;
            $this->loadModelsFromRequest($this->child_models, $sub_model_class);
            $this->child_models[] = new $sub_model_class;
            $this->child_models = array_slice($this->child_models, -1, 1, true);
        }
    }

    public function run($id = null) {
        $this->model = $this->getModel($id);
//        $this->findModels();
        $this->initModels();
        return $this->render();
    }

}