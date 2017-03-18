<?php
namespace dezmont765\yii2bundle\filters;

use Yii;
use yii\base\ActionFilter;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 05.03.2016
 * Time: 2:02
 */
class AjaxJsonFilter extends ActionFilter
{
    public function beforeAction($action) {
        if(parent::beforeAction($action)) {
            if(!Yii::$app->request->isAjax) {
                return false;
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return true;
        }
        else return false;
    }
}