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
class SelectionByAttributeAction extends SelectionAction
{
    public $delimiter = ',';
    public $is_multiple = false;
    public $query_param = 'id';


    public function run() {
        /** @var MainActiveRecord $model_class */
        /** @var MainActiveRecord $model */
        Yii::$app->response->format = Response::FORMAT_JSON;
        $value = Yii::$app->request->getQueryParam($this->query_param);
        $model_class = $this->getModelClass();
        $model = new $model_class;
        if($this->is_multiple) {
            $ids = explode($this->delimiter, $value);
            $models = $model->searchByIds($ids);
        }
        else {
            $models = $model->searchByAttribute($this->key, $value);
        }
        $model_array = [];
        if(count($models) == 1) {
            $model = array_shift($models);
            $model_array = $this->getItem($model);
        }
        else {
            foreach($models as $model) {
                $model_array[] = $this->getItem($model);
            }
        }
        return ['more' => false, 'results' => $model_array];
    }



}