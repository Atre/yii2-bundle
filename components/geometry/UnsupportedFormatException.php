<?php
namespace dezmont765\yii2bundle\components\geometry;
use Exception;

class UnsupportedFormatException extends Exception
{

    public function __construct($format)
    {
        $this->message = "This image format ($format) is not supported by your version of GD library";
    }
}