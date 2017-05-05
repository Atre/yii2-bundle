<?php

namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\actions\DynamicFieldsAction;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 30.04.2017
 * Time: 15:57
 */
class CreateWithDynamicChildrenAction extends MultipleDynamicFieldsAction
{

    public function getModel($id = null) {
        $this->model_class = $this->getModelClass();
        $model_class = $this->model_class;
        return new $model_class;
    }


    public function run($id = null) {
        parent::run($id);
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