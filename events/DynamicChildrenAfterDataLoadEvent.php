<?php
namespace dezmont765\yii2bundle\events;

use dezmont765\yii2bundle\actions\DynamicChildrenProcessor;
use yii\base\Event;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 18.05.2017
 * Time: 17:43
 */
class DynamicChildrenAfterDataLoadEvent extends Event
{
    public $field_processor = null;


    /**
     * DynamicChildrenAfterDataLoadEvent constructor.
     * @param DynamicChildrenProcessor $field_processor
     * @param array $config
     */
    public function __construct($field_processor, $config = []) {
        parent::__construct($config);
        $this->field_processor = $field_processor;
    }
}