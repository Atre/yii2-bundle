<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\controllers\MainController;
use yii\base\Action;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 01.12.2016
 * Time: 15:02
 * @property MainController $controller
 */
class MainAction extends Action
{

    public $success_message = null;
    public $error_message = null;
    public $model_class = null;


    public function getModelClass() {
        $model_class = $this->model_class !== null ? $this->model_class : $this->controller->getModelClass();
        return $model_class;
    }
}