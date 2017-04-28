<?php

namespace dezmont765\yii2bundle\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use yii\web\User;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 28.04.2017
 * Time: 15:29
 */
class OwnerBehavior extends Behavior
{
    public $created_by_attribute = 'created_by';
    public $modified_by_attribute = 'modified_by';


    public function events() {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }


    /**
     * @var User $user
     */
    private $user = null;


    private function getUser() {
        $this->user = Yii::$app->get('user', false);
        return $this->user instanceof User;
    }


    public function beforeInsert() {
        if($this->getUser()) {
            if($this->created_by_attribute != null) {
                $this->owner->{$this->created_by_attribute} = $this->user->id;
            }
        }
    }


    public function beforeSave() {
        $this->getUser();
        if($this->getUser()) {
            if($this->modified_by_attribute != null) {
                $this->owner->{$this->modified_by_attribute} = $this->user->id;
            }
        }
    }
}