<?php

namespace dezmont765\yii2bundle\controllers;

use dezmont765\yii2bundle\filters\LayoutFilter;
use dezmont765\yii2bundle\models\MainActiveRecord;
use dezmont765\yii2bundle\widgets\PartialActiveForm;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: DezMonT
 * Date: 24.03.2015
 * Time: 12:47
 * MainView $view
 */
class MainController extends Controller
{
    const ACCESS_FILTER = 'access';
    const TRACK_USER_FILTER = 'track_user_filter';
    const TRACK_USER_REQUEST_FILTER = 'track_user_request_filter';
    const PAGE_SAVER_FILTER = 'page_saver_filter';


    public function behaviors() {
        return [
            self::ACCESS_FILTER => [
                'class' => AccessControl::className(),
            ],
            //            self::PAGE_SAVER_FILTER => [
            //                'class' => PageSaver::className(),
            //            ]
        ];
    }


    public $activeMap = [];


    public function getTabsActivity() {
        return isset($this->activeMap[$this->action->id]) ? $this->activeMap[$this->action->id] : [];
    }


    public function checkAccess($permission, array $params = []) {
        if($permission === null) {
            return true;
        }
        if(Yii::$app->user->can($permission, $params)) {
            return true;
        }
        else {
            if(Yii::$app->user->getIsGuest()) {
                Yii::$app->user->loginRequired();
            }
            else {
                throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
            return false;
        }
    }


    public function performAjaxValidation($model) {
    }


    public function modelQuery($model_class, $id = null) {
        /**
         * @var MainActiveRecord|string $model_class
         */
        $query = $model_class::find();
        if($id) {
            $query->andWhere(['id' => $id]);
        }
        return $query;
    }


    /**
     * @param string|MainActiveRecord $model_class
     * @param $id
     * @param null|ActiveQuery $query
     * @param bool $is_safe
     * @return MainActiveRecord
     * @throws NotFoundHttpException
     */
    public function findModel($model_class, $id, ActiveQuery $query = null, $is_safe = true) {
        if(!$query instanceof ActiveQuery) {
            $model = $this->modelQuery($model_class, $id)->one();
        }
        else {
            $model = $query->one();
        }
        if(!$model instanceof $model_class && $is_safe) {
            throw new NotFoundHttpException(Yii::t('messages', 'The requested page does not exist.'));
        }
        else return $model;
    }


    public function selectionList($model_class, $attribute, array $additional_criteria = [], callable $return_wrap = null) {
        /** @var MainActiveRecord $model_class
         * @var MainActiveRecord $model
         */
        $value = Yii::$app->request->getQueryParam('value');
        $model = new $model_class;
        $models = $model->searchByAttribute($attribute, $value, $additional_criteria);
        $model_array = [];
        foreach($models as $model) {
            $model_array[] = ['id' => $model->id,
                              'text' => is_null($return_wrap) ? $model->$attribute : $return_wrap($model)];
        }
        echo json_encode(['more' => false, 'results' => $model_array]);
    }


    public function selectionById($model_class, $attribute = 'name', callable $return_wrap = null) {
        /** @var MainActiveRecord $model_class
         * @var MainActiveRecord $model
         */
        $id = Yii::$app->request->getQueryParam('id');
        $model = new $model_class;
        $ids = explode(',', $id);
        $models = $model->searchByIds($ids);
        $model_array = [];
        if(count($models) == 1) {
            $model = array_shift($models);
            $model_array = ['id' => $model->id,
                            'text' => is_null($return_wrap) ? $model->$attribute : $return_wrap($model)];
        }
        else {
            foreach($models as $model) {
                $model_array[] = ['id' => $model->id,
                                  'text' => is_null($return_wrap) ? $model->$attribute : $return_wrap($model)];
            }
        }
        echo json_encode(['more' => false, 'results' => $model_array]);
    }


    public function selectionByAttribute($model_class, $attribute, $show_attribute, $additional_criteria = null, callable $return_wrap = null) {
        /** @var MainActiveRecord $model_class
         * @var MainActiveRecord $model
         */
        $value = Yii::$app->request->getQueryParam('id');
        $model = new $model_class;
        $models = $model->searchByAttribute($attribute, $value, $additional_criteria);
        $model_array = [];
        if(count($models) == 1) {
            $model = array_shift($models);
            $model_array = ['id' => $model->$attribute, 'text' => $model->$show_attribute];
        }
        else {
            foreach($models as $model) {
                $model_array[] = ['id' => $model->$attribute, 'text' => $model->$show_attribute];
            }
        }
        echo json_encode(['more' => false, 'results' => $model_array]);
    }


    /**
     * @param MainActiveRecord $model
     * @return array|null
     */
    public function ajaxValidation($model) {
        $result = null;
        if(Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result = ActiveForm::validate($model);
        }
        return $result;
    }


    /**
     * @param MainActiveRecord[] $models
     * @param $attributes
     * @param array $models_without_id
     * @return array|null
     */
    public function ajaxValidationMultiple($models, $attributes, $models_without_id = []) {
        $result = null;
        if(Yii::$app->request->isAjax) {
            foreach($models as $model) {
                $model->load(Yii::$app->request->post());
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result = PartialActiveForm::validationMultiple($models, $attributes, $models_without_id);
        }
        return $result;
    }




    public function getModelClass() {
        return '';
    }


    public function getRoleToLayoutMap($role) {
        return [];
    }


    public function getLayoutByRole() {
        $role = LayoutFilter::getRole();
        $layouts = $this->getRoleToLayoutMap($role);
        return $layouts;
    }


    public function getActiveMap() {
        return [
        ];
    }
}