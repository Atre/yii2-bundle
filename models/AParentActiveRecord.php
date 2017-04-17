<?php
namespace dezmont765\yii2bundle\models;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.04.2017
 * Time: 17:00
 * @property $category
 */
abstract class AParentActiveRecord extends MainActiveRecord
{
    abstract public function getSubTableParentClass();


    static public function basicSubTableClass() {
        return null;
    }


    static public function basicSubTableView() {
        return null;
    }


    private $sub_table_relation = null;


    /**
     * @return \dezmont765\yii2bundle\models\ASubActiveRecord
     */
    public function getSubTableRelation() {
        $parent_class = $this->getSubTableParentClass();
        if($this->sub_table_relation instanceof $parent_class) {
            return $this->sub_table_relation;
        }
        $sub_table_relation_fields = static::subTablesRelationFields();
        if(isset($sub_table_relation_fields[$this->category])) {
            $sub_table_relation_field = $sub_table_relation_fields[$this->category];
        }
        else {
            return null;
        }
        $this->sub_table_relation = $this->$sub_table_relation_field;
        return $this->sub_table_relation;
    }


    public static function getSubTableClassByCategory($category) {
        if(isset(static::subTablesClasses()[$category])) {
            return static::subTablesClasses()[$category];
        }
        else {
            $basic_form_class = static::basicSubTableClass();
            return $basic_form_class;
        }
    }


    public static function getSubTableViewByCategory($category) {
        if(isset(static::subTableViews()[$category])) {
            return static::subTableViews()[$category];
        }
        else return static::basicSubTableView();
    }


    public function getSubTableFormNameByCategory($category) {
        /**
         * @var $model_class MainActiveRecord
         */
        $sub_table_relation_fields = static::subTablesClasses();
        if(isset($sub_table_relation_fields[$category])) {
            $model_class = $sub_table_relation_fields[$category];
            return $model_class::_formName();
        }
        else return null;
    }


    public static function subTablesClasses() {
        return [];
    }


    public static function subTableViews() {
        return [];
    }


    public static function subTablesRelationFields() {
        return [];
    }
}