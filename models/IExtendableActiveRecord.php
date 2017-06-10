<?php

namespace dezmont765\yii2bundle\models;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 08.05.2017
 * Time: 11:41
 */
interface IExtendableActiveRecord
{
    public function getDependentModelsParentClass();


    public static function dummyDependentModelsClass();
//
//
    public static function dummyDependentModelsView();


    public static function dependentModelsClasses();


    public static function dependentModelsViews();


    public static function dependentModelsRelationFields();


    public static function dependentModelsBaseView();

}