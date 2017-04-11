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
        $attribute = Yii::$app->request->get('attribute');
        $model = $this->controller->findModel($model_class, $id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model_array = $model->toArray();
        if($attribute && isset($model_array[$attribute])) {
            return $model_array[$attribute];
        }
        else return $model_array;
    }
}