<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\components\Alert;
use Exception;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 04.10.2016
 * Time: 15:40
 */
class DeleteAction extends MainAction
{

    public $search_model_class = null;
    public $error_message = 'Item has not been deleted';

    public function run($id) {
        try {
            $model_class = $this->getModelClass();
            $model = $this->controller->findModel($model_class, $id);
            $model->delete();
        }
        catch(Exception $e) {
            Alert::addError($this->error_message, $e->getMessage());
        }
        return $this->controller->redirect(['list']);
    }
}