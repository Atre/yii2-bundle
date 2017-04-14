<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\components\Alert;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 09.04.2017
 * Time: 13:56
 */
abstract class ManageAction extends MainAction
{

    const ON_MODEL_CLASS_RETRIEVING = 'on_model_class_retrieving';
    const ON_MODEL_RETRIEVING = 'on_model_retrieving';
    const ON_BEFORE_VALIDATION = 'on_before_validation';
    const ON_SUCCESSFUL_SAVE = 'on_successful_save';
    const ON_UNSUCCESSFUL_SAVE = 'on_unsuccessful_save';
    const ON_DEFAULT_RETURN = 'on_default_return';
    const IS_REDIRECT = 'is_redirect';
    const SUCCESSFUL_SAVE_REDIRECT_URL = 'successful_save_redirect_url';
    const UNSUCCESSFUL_SAVE_REDIRECT_URL = 'unsuccessful_save_redirect_url';

    public $on_model_class_retrieving = 'self::onModelClassRetrieving';
    public $on_model_retrieving = 'self::onModelRetrieving';
    public $on_before_validation = null;
    public $on_successful_save = 'self::onSuccessfulSave';
    public $on_unsuccessful_save = 'self::onUnsuccessfulSave';
    public $on_default_return = 'self::onDefaultReturn';
    public $is_ajax_validation = true;
    public $is_load_from_post = true;
    public $model_scenario = [];
    public $successful_save_redirect_url = ['list'];
    public $unsuccessful_save_redirect_url = ['list'];


    public function run($id = null) {
        $model_class = $this->modelClassRetrieving();
        $model = $this->modelRetrieving($model_class, $id);
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
                $response = $this->successfulSave($model);
                if(!empty($response)) {
                    return $response;
                }
            }
            else {
                $response = $this->unsuccessfulSave($model);
                if(!empty($response)) {
                    return $response;
                }
            }
        }
        return $this->defaultReturn($model);
    }


    protected function onSuccessfulSave() {
        if($this->is_redirect) {
            Alert::addSuccess('Item has been saved');
            return $this->controller->redirect($this->successful_save_redirect_url);
        }
        else return null;
    }


    private function successfulSave($model) {
        if(is_callable($this->on_successful_save)) {
            return call_user_func_array($this->on_successful_save,[$model]);
        }
        else return $this->onSuccessfulSave();
    }


    private function onUnsuccessfulSave() {
        if($this->is_redirect) {
            Alert::addError('Item has not been saved');
            return $this->controller->redirect($this->unsuccessful_save_redirect_url);
        }
        else return null;
    }


    private function unsuccessfulSave($model) {
        if(is_callable($this->on_unsuccessful_save)) {
            return call_user_func_array($this->on_unsuccessful_save,[$model]);
        }
        else return $this->onUnsuccessfulSave();
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


    protected function onModelClassRetrieving() {
        $model_class = $this->getModelClass();
        return $model_class;
    }


    private function modelClassRetrieving() {
        if(is_callable($this->on_model_class_retrieving)) {
            $model_class = call_user_func($this->on_model_class_retrieving);
        }
        else {
            $model_class = $this->onModelClassRetrieving();
        }
        return $model_class;
    }


    private function beforeValidation(&$model) {
        if(is_callable($this->on_before_validation)) {
            call_user_func_array($this->on_before_validation, [&$model]);
        }
    }


    abstract protected function onModelRetrieving($model_class, $id);


    private function modelRetrieving($model_class, $id = null) {
        if(is_callable($this->on_model_retrieving)) {
            $model_class = call_user_func($this->on_model_retrieving);
        }
        else {
            $model_class = $this->onModelRetrieving($model_class, $id);
        }
        return $model_class;
    }


    public function getDefaultView() {
        return $this->controller->id . '-form';
    }


}