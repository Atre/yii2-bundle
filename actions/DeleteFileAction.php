<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\components\FileSaveBehavior;
use dezmont765\yii2bundle\controllers\MainController;
use dezmont765\yii2bundle\models\MainActiveRecord;
use Yii;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 04.10.2016
 * Time: 15:40
 * @property MainController $controller
 */
class DeleteFileAction extends MainAction
{

    public function run($id) {
        /**@var MainActiveRecord|FileSaveBehavior $model */
        Yii::$app->response->format = Response::FORMAT_JSON;
        $file_name = \Yii::$app->request->getQueryParam('name');
        if(empty($file_name)) {
            return ['success' => false];
        }
        if(Yii::$app->request->isAjax) {
            $model_class = $this->getModelClass();
            $model = $this->controller->findModel($model_class, $id);
            if($model instanceof $model_class) {
                if($model->deleteFile($file_name)) {
                    return ['success' => true];
                }
            }
        }
        return ['success' => false];
    }
}