<?php
namespace dezmont765\yii2bundle\components\geometry;
use Exception;

class InvalidFileException extends Exception
{

    public function __construct($path)
    {
        $this->message = "Invalid file: $path";
    }
}