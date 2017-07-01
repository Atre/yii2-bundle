<?php

namespace dezmont765\yii2bundle\components;

use yii\helpers\VarDumper;
use yii\log\Target;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 29.06.2017
 * Time: 18:58
 */
class StdOutputTarget extends Target
{

    /**
     * Exports log [[messages]] to a specific destination.
     * Child classes must implement this method.
     */
    public function export() {
        foreach($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            if(!is_string($text)) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if($text instanceof \Throwable || $text instanceof \Exception) {
                    $text = (string)$text;
                }
                else {
                    $text = VarDumper::export($text);
                }
            }
            printf("
            Timestamp :%d \n
            Category : %s \n
            Level : %d \n
            Text : %s \n
            ", [$timestamp, $category, $level, $text]);
        }
    }
}