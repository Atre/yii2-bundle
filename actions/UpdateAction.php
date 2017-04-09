<?php
namespace dezmont765\yii2bundle\actions;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 04.10.2016
 * Time: 15:40
 */
class UpdateAction extends ManageAction
{


    protected function onModelRetrieving($model_class, $id) {
        $model = $this->controller->findModel($model_class, $id);
        return $model;
    }
}