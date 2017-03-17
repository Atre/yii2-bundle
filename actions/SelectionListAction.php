<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\models\MainActiveRecord;
use Yii;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 28.11.2016
 * Time: 16:27
 */
class SelectionListAction extends SelectionAction
{
    public $query_param = 'value';


    public function run() {
        /** @var MainActiveRecord $model_class */
        /** @var MainActiveRecord $model */
        Yii::$app->response->format = Response::FORMAT_JSON;
        $value = Yii::$app->request->getQueryParam($this->query_param);
        $model_class = $this->getModelClass();
        $model = new $model_class;
        $models = $model->searchByAttribute($this->attribute, $value);
        $model_array = [];
        foreach($models as $model) {
            $model_array[] = $this->getItem($model);
        }
        return ['more' => false, 'results' => $model_array];
    }



}