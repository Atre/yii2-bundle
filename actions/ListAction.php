<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\models\MainActiveRecord;
use dezmont765\yii2bundle\traits\TSearch;
use Yii;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 04.10.2016
 * Time: 15:40
 * @property TSearch $search_model|string
 */
class ListAction extends MainAction
{
    const SEARCH_QUERY_FUNCTION = 'search_query_function';

    public $search_query_function = 'baseSearchQuery';


    public function run() {
        /** @var TSearch|MainActiveRecord $search_model */
        $search_class = $this->getModelClass();
        $additional_params = [];
        foreach($this->additional_params as $additional_param) {
            $additional_params[$additional_param] = Yii::$app->request->get($additional_param);
        }
        $search_model = new $search_class;
        $search_model->load(Yii::$app->request->queryParams);
        $dataProvider = $search_model->search($search_model->{$this->search_query_function}());
        return $this->controller->{$this->render_method}($this->getView(), [
                                                                               'searchModel' => $search_model,
                                                                               'dataProvider' => $dataProvider,
                                                                           ] + $additional_params);
    }


    public function getDefaultView() {
        return $this->controller->id . '-list';
    }
}