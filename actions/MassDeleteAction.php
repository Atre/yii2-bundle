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
class MassDeleteAction extends MainAction
{

    public $search_model_class = null;
    public $param = 'keys';
    public $success_message = "Items has been successfully deleted";
    public $error_message = "Item has not been deleted";


    public function run() {
        if(isset($_POST[$this->param])) {
            $item_keys = \Yii::$app->request->post();
            foreach($item_keys as $key) {
                try {
                    $model_class = $this->getModelClass();
                    $model = $this->controller->findModel($model_class, $key);
                    if($model) {
                        if($model->delete()) {
                            Alert::addSuccess($this->success_message);
                        }
                    }
                }
                catch(Exception $e) {
                    Alert::addError($this->error_message, $e->getMessage());
                }
            }
        }
        return $this->controller->redirect(['list']);
    }
}