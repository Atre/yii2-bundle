<?php

namespace dezmont765\yii2bundle\actions;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 30.04.2017
 * Time: 21:22
 */
class UpdateWithDynamicChildrenAction extends MultipleDynamicFieldsAction
{


    public function getModel($id = null) {
        $this->model_class = $this->getModelClass();
        return $this->controller->findModel($this->model_class, $id);
    }


    public function run($id = null) {
        parent::run($id);
        $this->findExistingSubModels();
        $this->initModels();
        $models = [$this->model];
        foreach($this->fields as $field) {
            $models = array_merge($models, $field[self::SUB_MODELS]);
        }
        $result = $this->controller->ajaxValidationMultiple($models, null,
                                                            [$this->model_class]);
        if($result !== null) {
            return $result;
        }
        $this->save();
        return $this->controller->render($this->getView(), ['model' => $this->model]);
    }
}