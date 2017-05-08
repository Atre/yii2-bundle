<?php

namespace dezmont765\yii2bundle\actions;
class AddDynamicFieldsAction extends LoadSingleDynamicFieldsAction
{
    public function initModels() {
        if($this->sub_model_class !== null) {
            $sub_model_class = $this->sub_model_class;
            $this->loadModelsFromRequest($this->sub_models, $sub_model_class);
            $this->sub_models[] = new $sub_model_class;
            $this->sub_models = array_slice($this->sub_models, -1, 1, true);
        }
    }

    public function run($id = null) {
        $this->model = $this->getModel($id);
//        $this->findModels();
        $this->initModels();
        return $this->render();
    }

}