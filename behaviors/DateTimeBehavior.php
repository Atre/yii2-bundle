<?php

namespace dezmont765\yii2bundle\behaviors;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 28.04.2017
 * Time: 15:38
 */
class DateTimeBehavior extends Behavior
{
    public $created_at_attribute = 'created_at';
    public $updated_at = 'updated_at';
    public $format = 'Y-m-d H:i:s';


    public function events() {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }


    public function beforeInsert() {
        $this->owner->{$this->created_at_attribute} = date($this->format);
    }


    public function beforeSave() {
        $this->owner->{$this->updated_at} = date($this->format);
    }
}