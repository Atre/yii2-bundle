<?php
namespace dezmont765\yii2bundle\models;

use ReflectionClass;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 12.05.2016
 * Time: 11:07
 */
abstract class ADependentActiveRecord extends MainActiveRecord implements IDependentActiveRecord
{




    public function getMainModelBindingAttribute() {
        return 'id';
    }


    public function getSelfBindingAttribute() {
        return 'id';
    }


    public static function getParentBindingAttribute() {
        return null;
    }
    public static function getParentBindingClass() {
        return null;
    }


    public function setChangedAttributes($attributes) {
        $this->_changed_attributes = array_merge($this->_changed_attributes, $attributes);
    }


    public function saveConnectedModel() {
        /** @var MainActiveRecord $main_class */
        $main_class = $this->getConnectedModelClass();
        $main_attribute = $this->getConnectedModelAttribute();
        if(!$this->$main_attribute instanceof $main_class) {
            $this->$main_attribute =
                $main_class::findOne([$this->getMainModelBindingAttribute() => $this->getSelfBindingAttribute()]);
            if(!$this->$main_attribute instanceof $main_class) {
                $this->$main_attribute = new $main_class();
            }
        }
        $this->$main_attribute->attributes = $this->getAttributes();
        $saved = $this->$main_attribute->save();
        $this->changedAttributes = $this->$main_attribute->changedAttributes;
        return $saved;
    }


    public function getAttributes($names = null, $except = []) {
        $values = [];
        if($names === null) {
            $names = $this->attributes();
        }
        $names = array_merge($names, $this->safeAttributes());
        $class = new ReflectionClass($this);
        foreach($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if(!$property->isStatic()) {
                $names[] = $property->getName();
            }
        }
        foreach($names as $name) {
            $values[$name] = $this->$name;
        }
        foreach($except as $name) {
            unset($values[$name]);
        }
        return $values;
    }


    public function saveInternal($insert) {
        if(!$this->beforeSave($insert)) {
            return false;
        }
        $this->afterSave($insert, []);
        return true;
    }


    public function insertInternal($attributes = null) {
        $main_class = $this->getConnectedModelClass();
        $main_attribute = $this->getConnectedModelAttribute();
        $this->saveConnectedModel();
        if($this->$main_attribute instanceof $main_class && $this->tableName() == $this->$main_attribute->tableName()) {
            return $this->saveInternal(true);
        }
        else {
            return parent::insertInternal($attributes);
        }
    }


    public function updateInternal($attributes = null) {
        $main_class = $this->getConnectedModelClass();
        $main_attribute = $this->getConnectedModelAttribute();
        $this->saveConnectedModel();
        if($this->$main_attribute instanceof $main_class && $this->tableName() == $this->$main_attribute->tableName()) {
            return $this->saveInternal(false);
        }
        else {
            return parent::updateInternal($attributes);
        }
    }


    public function afterFind() {
        parent::afterFind();
        $main_class = $this->getConnectedModelClass();
        $main_attribute = $this->getConnectedModelAttribute();
        if($this->$main_attribute instanceof $main_class) {
            $this->setAttributes($this->$main_attribute->attributes);
            $this->setOldAttributes($this->$main_attribute->attributes);
        }
    }


    public function beforeSave($insert) {
        if(parent::beforeSave($insert)) {
            $main_class = $this->getConnectedModelClass();
            $main_attribute = $this->getConnectedModelAttribute();
            if($this->$main_attribute instanceof $main_class) {
                $this->attributes = $this->$main_attribute->attributes;
            }
            return true;
        }
        else return false;
    }


}