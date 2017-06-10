<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 10.06.2017
 * Time: 16:17
 */
namespace dezmont765\yii2bundle\events;
use yii\base\Event;

class DynamicChildrenActionAfterSaveEvent extends Event
{
    public $result = null;
    public $model = null;
   public function __construct(array $config = []) {
       parent::__construct($config);
   }
}