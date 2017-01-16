<?php
namespace dezmont765\yii2bundle\components\geometry;
use Exception;

class FileAlreadyExistsException extends Exception
{
    public function __construct($path)
    {
        $this->message = "File $path is already exists!";
    }
}