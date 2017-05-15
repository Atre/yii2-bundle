<?php
namespace dezmont765\yii2bundle\behaviors;

use dezmont765\yii2bundle\models\AParentActiveRecord;
use dezmont765\yii2bundle\models\MainActiveRecord;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 11.05.2017
 * Time: 14:01
 */
class SubModelsBehavior extends Behavior
{
    public $sub_models = [];

    public $parent_binding_attribute = 'id';
    public $child_binding_attribute = 'id';
//    public function attach($owner) {
//        if(empty($this->parent_binding_attribute) || empty($this->child_binding_attribute)) {
//            throw new \yii\base\InvalidParamException('Binding attributes should be defined');
//        }
//        parent::attach($owner); // TODO: Change the autogenerated stub
//    }
    public function events() {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
        ];
    }


    public function afterFind() {
        if($this->owner instanceof AParentActiveRecord) {
            foreach($this->owner->subTablesRelationFields() as $category => $relationField) {
                $sub_table_class = $this->owner->getSubTableClassByCategory($category);
                if($this->owner->{$relationField} instanceof MainActiveRecord) {
                    $this->sub_models[$sub_table_class] = $this->owner->{$relationField};
                }
                else {
                    $this->sub_models[$sub_table_class] = new $sub_table_class;
                }
            }
        }
    }


    public function setAttributes($category, $attributes) {
        if(isset($this->sub_models[$category]) && $this->sub_models[$category] instanceof MainActiveRecord) {
            $this->sub_models[$category]->attrributes = $attributes;
        }
    }
//
//
//    public function &getSubModelByCategory($category) {
//        if(isset($this->sub_models[$category])) {
//            return $this->sub_models[$category];
//        }
//        else {
//            $sub_table_class = $this->owner->getSubTableClassByCategory($category);
//            $this->sub_models[$category] = new $sub_table_class;
//        }
//    }


    public function afterSave() {
        if($this->owner instanceof AParentActiveRecord) {
            foreach($this->sub_models as $sub_model) {
                $sub_model->{$this->child_binding_attribute} = $this->owner->{$this->parent_binding_attribute};
                $sub_model->save();
            }
        }
    }
}