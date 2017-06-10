<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 10.06.2017
 * Time: 13:19
 */
namespace dezmont765\yii2bundle\events;

use yii\base\Event;

class DynamicChildrenActionBeforeSaveEvent extends Event
{
    public $model = null;
    public $is_valid = true;
}