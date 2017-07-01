<?php

namespace dezmont765\yii2bundle\components;

use Yii;
use yii\console\Application;
use yii\log\Logger;

/**
 * Created by PhpStorm.
 * User: DezMonT
 * Date: 29.09.2015
 * Time: 15:56
 */
class EchoLogger
{
    public static $silence = true;
    public static $buffer = [];
    public static $counter = [];
    public static $sum = [];


    public static function start($silence) {
        self::$silence = $silence;
        self::$buffer = [];
    }


    public static function _echo($label, $value, $is_bold = false) {
        $info = "$label : $value";
        if(!self::$silence) {
            if($is_bold) {
                $info = "<b>$info</b>";
            }
            $info = "<span>$info</span>";
        }
        self::$buffer[] = $info;
        self::addCount($label);
        self::addSum($label, $value);
        return $info;
    }


    public static function error($label, $value, $category = 'application') {
        $info = self::_echo($label, $value);
        Yii::getLogger()->log($info, Logger::LEVEL_ERROR, $category);
    }


    public static function _var_dump($label, $data) {
        ob_start();
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        self::$buffer[] = ob_get_contents();
        ob_end_clean();
        self::addCount($label);
        self::addSum($label, $data);
    }


    private static function addCount($label) {
        if(!isset(self::$counter[$label])) {
            self::$counter[$label] = 0;
        }
        self::$counter[$label] += 1;
    }


    private static function addSum($label, $data) {
        if(!isset(self::$sum[$label])) {
            self::$sum[$label] = 0;
        }
        self::$sum[$label] += (int)$data;
    }


    public static function getCount($label) {
        if(isset(self::$counter[$label])) {
            return self::$counter[$label];
        }
        else return 0;
    }


    public static function getSum($label) {
        if(isset(self::$sum[$label])) {
            return self::$sum[$label];
        }
        else return 0;
    }


    public static function end() {
        if(self::$silence) {
            Yii::getLogger()->log(implode('\r\n', self::$buffer), Logger::LEVEL_INFO);
        }
        else {
            echo implode('<br>', self::$buffer);
        }
    }


    public static function _printf($message, ...$args) {
        Yii::info(vsprintf($message, $args), 'debug');
        if(Yii::$app instanceof Application) {
            vprintf($message . "\n", $args);
        }
    }


    public static function _return($delimiter = '\r\n') {
        return implode($delimiter, self::$buffer);
    }
}