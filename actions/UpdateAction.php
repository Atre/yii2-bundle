<?php
namespace dezmont765\yii2bundle\actions;

use Yii;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 04.10.2016
 * Time: 15:40
 */
class UpdateAction extends MainAction
{


    public function run($id) {
        $model_class = $this->getModelClass();
        $model = $this->controller->findModel($model_class, $id);
        $this->controller->checkAccess($this->permission, ['model' => $model]);
        if($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->controller->redirect(['list']);
        }
        else {
            return $this->controller->render($this->getView(), [
                'model' => $model,
            ]);
        }
    }


    public function getDefaultView() {
        return $this->controller->id . '-form';
    }
}