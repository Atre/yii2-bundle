<?php
namespace dezmont765\yii2bundle\actions;

use Yii;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.03.2017
 * Time: 0:40
 */
class AsJsonAction extends MainAction
{
    public function run($id) {
        $model_class = $this->getModelClass();
        $model = $this->controller->findModel($model_class, $id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $model->toArray();
    }
}