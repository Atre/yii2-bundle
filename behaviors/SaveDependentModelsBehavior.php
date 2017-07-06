<?php
namespace dezmont765\yii2bundle\behaviors;

use dezmont765\yii2bundle\models\AExtendableActiveRecord;
use dezmont765\yii2bundle\models\MainActiveRecord;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 11.05.2017
 * Time: 14:01
 * @property AExtendableActiveRecord $owner
 */
class SaveDependentModelsBehavior extends Behavior
{
    public $parent_binding_attribute = 'id';
    public $child_binding_attribute = 'id';


    public function events() {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
        ];
    }


    public function afterSave() {
        if($this->owner instanceof AExtendableActiveRecord) {
            $this->owner->dependentModel->{$this->child_binding_attribute} = $this->owner->{$this->parent_binding_attribute};
            $this->owner->dependentModel->save();
        }
    }
}