<?php
namespace dezmont765\yii2bundle\components\geometry;
use Exception;

class FileNotSaveException extends Exception
{
    public function __construct($path)
    {
        $this->message = "File: $path not saved";
    }
}
?>