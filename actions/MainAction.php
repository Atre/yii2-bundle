<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\controllers\MainController;
use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\widgets\ActiveForm;

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
    public $view = null;
    public $permission = null;
    public $permission_params = null;
    public $render_method = 'render';
    public $additional_params = [];


    public function ajaxValidation($model) {
        $result = null;
        if(Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result = ActiveForm::validate($model);
        }

        return $result;
    }


    public function getModelClass() {
        $model_class = $this->model_class !== null ? $this->model_class : $this->controller->getModelClass();
        return $model_class;
    }


    public function getView() {
        $view = $this->view !== null ? $this->view : $this->getDefaultView();
        return $view;
    }


    public function getDefaultView() {
        return '';
    }
}