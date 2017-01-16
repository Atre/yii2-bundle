<?php

namespace dezmont765\yii2bundle\components\geometry;

use Exception;

class IllegalArgumentException extends Exception
{
    public function __consruct()
    {
        $this->message = "Illegal argument";
    }
}
?>