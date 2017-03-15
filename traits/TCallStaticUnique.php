<?php
namespace dezmont765\yii2bundle\traits;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 20.12.2016
 * Time: 12:06
 */
trait TCallStaticUnique
{
    public static function __callStatic($name, $arguments) {
        return get_called_class().$name;
    }
}