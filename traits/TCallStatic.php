<?php
namespace dezmont765\yii2bundle\traits;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 20.12.2016
 * Time: 12:06
 */
trait TCallStatic
{
    public static function __callStatic($name, $arguments) {
        return $name;
    }
}