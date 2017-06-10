<?php
namespace dezmont765\yii2bundle\models;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.04.2017
 * Time: 17:00
 * @property $category
 * @property MainActiveRecord $subModel
 */
abstract class AExtendableActiveRecord extends MainActiveRecord implements IExtendableActiveRecord
{


    private $sub_model = null;


    /**
     * @return \dezmont765\yii2bundle\models\ADependentActiveRecord
     */
    public function getSubModel() {
        $parent_class = $this->getDependentModelsParentClass();
        if($this->sub_model instanceof $parent_class) {
            return $this->sub_model;
        }
        $sub_table_relation_field = $this->getSubTableRelationFieldByCategory($this->category);
        if($sub_table_relation_field === null) {
            return null;
        }
        $this->sub_model = $this->$sub_table_relation_field;
        $sub_table_class = self::getSubTableClassByCategory($this->category);
        if(!$this->sub_model instanceof $sub_table_class) {
            $this->sub_model = new $sub_table_class;
        }
        return $this->sub_model;
    }


    public function setSubModel($sub_model) {
        $this->sub_model = $sub_model;
    }


    public static function getSubTableRelationFieldByCategory($category) {
        $sub_table_relation_fields = static::dependentModelsRelationFields();
        if(isset($sub_table_relation_fields[$category])) {
            return $sub_table_relation_fields[$category];
        }
        else {
            return null;
        }
    }


    /**
     * @param $category
     * @return ADependentActiveRecord|string
     */
    public static function getSubTableClassByCategory($category) {
        if(isset(static::dependentModelsClasses()[$category])) {
            return static::dependentModelsClasses()[$category];
        }
        else {
            $basic_form_class = static::dummyDependentModelsClass();
            return $basic_form_class;
        }
    }


    public static function getSubTableViewByCategory($category) {
        if(isset(static::dependentModelsViews()[$category])) {
            return static::dependentModelsViews()[$category];
        }
        else return static::dummyDependentModelsView();
    }


    public function getSubTableFormNameByCategory($category) {
        /**
         * @var $model_class MainActiveRecord
         */
        $sub_table_relation_fields = static::dependentModelsClasses();
        if(isset($sub_table_relation_fields[$category])) {
            $model_class = $sub_table_relation_fields[$category];
            return $model_class::_formName();
        }
        else return null;
    }


    public static function dummyDependentModelsClass() {
        return null;
    }


    public static function dummyDependentModelsView() {
        return null;
    }


}