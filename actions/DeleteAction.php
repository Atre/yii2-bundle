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

    public $error_message = 'Item has not been deleted';
    public $is_redirect = true;
    public $redirect_url = ['list'];


    public function run($id) {
        try {
            $model_class = $this->getModelClass();
            $model = $this->controller->findModel($model_class, $id);
            $this->controller->checkAccess($this->permission, ['model' => $model]);
            $model->delete();
        }
        catch(Exception $e) {
            Alert::addError($this->error_message, $e->getMessage());
        }
        if($this->is_redirect) {
            return $this->controller->redirect($this->redirect_url);
        }
        else return true;
    }

}