<?php
namespace dezmont765\yii2bundle\components\geometry;
use Exception;

class InvalidChannelException extends Exception
{

    public function __construct($chenalName)
    {
        $this->message = "Invalid channel: {$chenalName}";
    }
}