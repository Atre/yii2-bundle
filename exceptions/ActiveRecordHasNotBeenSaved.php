<?php

namespace dezmont765\yii2bundle\exceptions;

use Throwable;
use yii\base\Exception;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 28.06.2017
 * Time: 17:00
 */
class ActiveRecordHasNotBeenSaved extends Exception
{
    public $errors = [];


    public function __construct($message = "", $errors, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

}