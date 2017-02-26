<?php
namespace dezmont765\yii2bundle\events;
use yii\base\Event;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 20.01.2017
 * Time: 19:59
 */
class FileSaveEvent extends Event
{
    const FILE_ATTRIBUTE = 'file_attribute';
    const FILE_PATH = 'file_path';
    public $file_attribute = null;
    public $file_path = null;
}