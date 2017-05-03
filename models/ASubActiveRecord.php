<?php
namespace dezmont765\yii2bundle\models;

use ReflectionClass;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 12.05.2016
 * Time: 11:07
 */
abstract class ASubActiveRecord extends MainActiveRecord
{
    const ALL = 'all';


    abstract public function getMainModelClass();


    static public function getMainModelAttribute() {
        return '';
    }


    public function getMainModelBindingAttribute() {
        return 'id';
    }


    public function getSelfBindingAttribute() {
        return 'id';
    }


    public function setChangedAttributes($attributes) {
        $this->_changed_attributes = array_merge($this->_changed_attributes, $attributes);
    }


    public function saveMainModel() {
        /** @var MainActiveRecord $main_class */
        $main_class = $this->getMainModelClass();
        $main_attribute = $this->getMainModelAttribute();
        if(!$this->$main_attribute instanceof $main_class) {
            $this->$main_attribute =
                $main_class::findOne([$this->getMainModelBindingAttribute() => $this->getSelfBindingAttribute()]);
            if(!$this->$main_attribute instanceof $main_class) {
                $this->$main_attribute = new $main_class();
            }
        }
//        if($this->tableName() !== $this->$main_attribute->tableName()) {
        $this->$main_attribute->attributes = $this->getAttributes();
        $saved = $this->$main_attribute->save();
        $this->changedAttributes = $this->$main_attribute->changedAttributes;
        return $saved;
//        }
//        else {
//            $this->$main_attribute->attributes = $this->attributes;
//            $this->changedAttributes = $this->$main_attribute->changedAttributes;
//            return false;
//        }
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


    public function insertInternal($attributes = null) {
        $main_class = $this->getMainModelClass();
        $main_attribute = $this->getMainModelAttribute();
        $this->saveMainModel();
        if($this->$main_attribute instanceof $main_class && $this->tableName() == $this->$main_attribute->tableName()) {
            if(!$this->beforeSave(true)) {
                return false;
            }
            $this->afterSave(true, []);
            return true;
        }
        else {
            return parent::insertInternal($attributes);
        }
    }


    public function updateInternal($attributes = null) {
        $main_class = $this->getMainModelClass();
        $main_attribute = $this->getMainModelAttribute();
        $this->saveMainModel();
        if($this->$main_attribute instanceof $main_class && $this->tableName() == $this->$main_attribute->tableName()) {
            if(!$this->beforeSave(true)) {
                return false;
            }
            $this->afterSave(true, []);
            return true;
        }
        else {
            return parent::updateInternal($attributes);
        }
    }


    public function afterFind() {
        parent::afterFind();
        $main_class = $this->getMainModelClass();
        $main_attribute = $this->getMainModelAttribute();
        if($this->$main_attribute instanceof $main_class) {
            $this->setAttributes($this->$main_attribute->attributes);
            $this->setOldAttributes($this->$main_attribute->attributes);
        }
    }


    public function beforeSave($insert) {
        if(parent::beforeSave($insert)) {
            $main_class = $this->getMainModelClass();
            $main_attribute = $this->getMainModelAttribute();
            if($this->$main_attribute instanceof $main_class) {
                $this->attributes = $this->$main_attribute->attributes;
            }
            return true;
        }
        else return false;
    }


}