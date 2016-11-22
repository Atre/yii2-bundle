<?php
/**
 * Created by PhpStorm.
 * User: DezMonT
 * Date: 24.03.2015
 * Time: 14:00
 */

namespace dezmont765\yii2bundle\filters;

use dezmont765\yii2bundle\controllers\MainController;
use dezmont765\yii2bundle\views\MainView;
use yii\base\ActionFilter;
use yii\rbac\Role;

class LayoutFilter extends ActionFilter
{
    public static $layout = 'main';
    public static $role = null;

    public static  function getActiveMap()
    {
        return [];
    }

    public static function getParams()
    {
        return $_GET;
    }
    public function beforeAction($action)
    {
        /**@var MainView $view
         * @var MainController $controller
         */
        $controller = $action->controller;
        $view = $action->controller->getView();
        $controller->activeMap = array_merge(static::getActiveMap(),$controller->activeMap);
        $action->controller->layout = static::$layout;
        $view->setLayoutData(static::layout($controller->getTabsActivity()));
        return parent::beforeAction($action);
    }

    public static function layout(array $active = [])
    {
        return [];
    }



    /**
     * @return string
     * return the role of user
     */
    public static function getRole()
    {
        if(self::$role === null)
        {
            if(\Yii::$app->user->isGuest)
            {
                self::$role = "Guest";
            }
            else
            {
                $role = \Yii::$app->authManager->getRole(\Yii::$app->user->identity->role);
                if($role instanceof Role)
                {
                    self::$role =  $role->name;
                }
                else self::$role = 'Guest';
            }
        }
        return self::$role;
    }

    public static  function getActive(array $active = [],$tab)
    {
        $active = array_flip($active);
        return (isset($active[$tab]) ? true : false);
    }

    public static function __callStatic($name,$attributes)
    {
        return get_called_class().$name;
    }

}