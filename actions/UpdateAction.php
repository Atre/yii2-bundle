<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\components\Alert;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 04.10.2016
 * Time: 15:40
 */
class UpdateAction extends MainAction
{
    public $on_success_save = ['dezmont765\yii2bundle\actions\UpdateAction', 'onSuccessSave'];
    public $on_default_return = ['dezmont765\yii2bundle\actions\UpdateAction', 'onDefaultReturn'];
    public $on_model_retrieving = ['dezmont765\yii2bundle\actions\UpdateAction', 'onModelRetrieving'];
    public $on_before_validation = null;
    public $is_ajax_validation = true;
    public $is_load_from_post = true;


    public function run($id) {
        $model_class = $this->modelRetrieving();
        $model = $this->controller->findModel($model_class, $id);
        $this->beforeValidation($model);
        if($this->is_ajax_validation) {
            $result = parent::ajaxValidation($model);
            if($result !== null) {
                return $result;
            }
        }
        $this->controller->checkAccess($this->permission, ['model' => $model]);
        if($this->is_load_from_post) {
            if($model->load(\Yii::$app->request->post())) {
                $should_model_be_saved = true;
            }
            else $should_model_be_saved = false;
        }
        else $should_model_be_saved = true;
        if($should_model_be_saved) {
            if($model->save()) {
                return $this->successSave();
            }
        }
        return $this->defaultReturn($model);
    }


    public function getDefaultView() {
        return $this->controller->id . '-form';
    }


    protected function onSuccessSave() {
        Alert::addSuccess('Item has been saved');
        return $this->controller->redirect(['list']);
    }


    private function successSave() {
        if(is_callable($this->on_success_save)) {
            return call_user_func($this->on_success_save);
        }
        else return $this->onSuccessSave();
    }


    protected function onDefaultReturn($model) {
        return $this->controller->render($this->getView(), ['model' => $model]);
    }


    private function defaultReturn($model) {
        if(is_callable($this->on_default_return)) {
            return call_user_func_array($this->on_default_return, [$model]);
        }
        else return $this->onDefaultReturn($model);
    }


    protected function onModelRetrieving() {
        $model_class = $this->getModelClass();
        return $model_class;
    }


    private function modelRetrieving() {
        if(is_callable($this->on_model_retrieving)) {
            $model_class = call_user_func($this->on_model_retrieving);
        }
        else {
            $model_class = $this->onModelRetrieving();
        }
        return $model_class;
    }


    private function beforeValidation(&$model) {
        if(is_callable($this->on_before_validation)) {
            call_user_func_array($this->on_before_validation, [&$model]);
        }
    }
}