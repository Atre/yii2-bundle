<?php
namespace dezmont765\yii2bundle\models;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 08.05.2017
 * Time: 11:44
 */
interface IDependentActiveRecord
{
    const ALL = 'all';


    /**
     * @return IExtendableActiveRecord
     */
    public function getExtendableModelClass();


    public static function getExtendableModelAttribute();


    public static function getParentBindingAttribute();


    public static function getParentBindingClass();

}