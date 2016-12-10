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
class ActionList extends MainAction
{

    public $search_model_class = null;


    public function run() {
        /** @var TSearch|MainActiveRecord $search_model */
        $search_class = $this->search_model_class;
        $search_model = new $search_class;
        $search_model->load(Yii::$app->request->queryParams);
        $dataProvider = $search_model->search();
        return $this->controller->render($this->controller->id . '-list', [
            'searchModel' => $search_model,
            'dataProvider' => $dataProvider,
        ]);
    }
}