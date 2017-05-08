<?php

namespace dezmont765\yii2bundle\models;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 08.05.2017
 * Time: 11:44
 */
interface ISubActiveRecord
{
    const ALL = 'all';


    public function getMainModelClass();


    public static function getMainModelAttribute();

}