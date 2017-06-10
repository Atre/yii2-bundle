<?php
namespace dezmont765\yii2bundle\helpers;

use yii\helpers\ArrayHelper;
use yii\helpers\ReplaceArrayValue;
use yii\helpers\UnsetArrayValue;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 10.06.2017
 * Time: 15:37
 */
class EnhancedArrayHelper extends ArrayHelper
{
    /**
     * Merges two or more arrays into one recursively.
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     * You can use [[UnsetArrayValue]] object to unset value from previous array or
     * [[ReplaceArrayValue]] to force replace former value instead of recursive merging.
     * @param array $a array to be merged to
     * @param array $b array to be merged from. You can specify additional
     * arrays via third argument, fourth argument etc.
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function merge($a, $b) {
        $args = func_get_args();
        $res = array_shift($args);
        while(!empty($args)) {
            $next = array_shift($args);
            foreach($next as $k => $v) {
                if($v instanceof UnsetArrayValue) {
                    unset($res[$k]);
                }
                elseif($v instanceof ReplaceArrayValue) {
                    $res[$k] = self::merge($res[$k],$v);
                }
                elseif(is_int($k)) {
                    if(isset($res[$k])) {
                        $res[] = $v;
                    }
                    else {
                        $res[$k] = $v;
                    }
                }
                elseif(is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::merge($res[$k], $v);
                }
                else {
                    $res[$k] = $v;
                }
            }
        }
        return $res;
    }
}