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
    public function getConnectedModelClass();


    public static function getConnectedModelAttribute();


    public static function getParentBindingAttribute();


    public static function getParentBindingClass();

}