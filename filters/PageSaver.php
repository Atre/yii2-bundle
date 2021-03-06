<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 03.03.2016
 * Time: 15:43
 */
namespace dezmont765\yii2bundle\filters;

use Yii;
use yii\base\ActionFilter;

class PageSaver extends ActionFilter
{
    public function afterAction($action, $result) {
        $result = parent::afterAction($action, $result);
        if(!Yii::$app->request->isAjax) {
            Yii::$app->user->setReturnUrl([Yii::$app->controller->id . '/' . $action->id] + $_GET);
        }
        return $result;
    }
}