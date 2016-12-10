<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\models\MainActiveRecord;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 04.10.2016
 * Time: 15:40
 */
class CreateAction extends MainAction
{


    public function run() {
        /** @var $model_class MainActiveRecord|string */
        /** @var $model MainActiveRecord */
        $model_class = $this->getModelClass();
        $model = new $model_class(['scenario' => 'create']);
        if($model->load(\Yii::$app->request->post())) {
            if($model->save()) {
                return $this->controller->redirect(['list']);
            }
        }
        return $this->controller->render($this->controller->id . '-form', ['model' => $model]);
    }
}