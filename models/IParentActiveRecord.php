<?php

namespace dezmont765\yii2bundle\models;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 08.05.2017
 * Time: 11:41
 */
interface IParentActiveRecord
{
    public function getSubTableParentClass();


//    public static function basicSubTableClass();
//
//
//    public static function basicSubTableView();


    public static function subTablesClasses();


    public static function subTableViews();


    public static function subTablesRelationFields();


    public static function subTableBaseView();
}