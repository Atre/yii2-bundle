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


    public function run() {
        /** @var TSearch|MainActiveRecord $search_model */
        $search_class = $this->getModelClass();
        $search_model = new $search_class;
        $search_model->load(Yii::$app->request->queryParams);
        $dataProvider = $search_model->search($search_model->baseSearchQuery());
        return $this->controller->render($this->getView(), [
            'searchModel' => $search_model,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function getDefaultView() {
        return $this->controller->id . '-list';
    }
}