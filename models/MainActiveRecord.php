<?php
/**
 * Created by PhpStorm.
 * User: DezMonT
 * Date: 12.04.2015
 * Time: 17:42
 */
namespace dezmont765\yii2bundle\models;

use DateTime;
use dezmont765\yii2bundle\components\Alert;
use Exception;
use ReflectionClass;
use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\StaleObjectException;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;

/**
 *
 * @class MainActiveRecord
 * @property Transaction $transaction
 * @property array $changedAttributes
 * @property mixed is_deleted
 */
class MainActiveRecord extends ActiveRecord
{
    public $is_saved = null;
    public $is_deleted = null;
    private $transaction = null;
    private $_oldAttributes;
    protected $_changed_attributes = [];


    public function getChangedAttributes() {
        return $this->_changed_attributes;
    }


    public function setChangedAttributes($attributes) {
        $this->_changed_attributes = $attributes;
    }


    protected function updateInternal($attributes = null) {
        if(!$this->beforeSave(false)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if(empty($values)) {
            $this->afterSave(false, $values);
            return 0;
        }
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if($lock !== null) {
            $values[$lock] = $this->$lock + 1;
            $condition[$lock] = $this->$lock;
        }
        // We do not check the return value of updateAll() because it's possible
        // that the UPDATE statement doesn't change anything and thus returns 0.
        $rows = $this->updateAll($values, $condition);
        if($lock !== null && !$rows) {
            throw new StaleObjectException('The object being updated is outdated.');
        }
        if(isset($values[$lock])) {
            $this->$lock = $values[$lock];
        }
        $changedAttributes = [];
        foreach($values as $name => $value) {
            $changedAttributes[$name] =
                isset($this->_oldAttributes[$name])
                // it is the additional check for case when an attribute was null
                && array_key_exists($name, $this->_oldAttributes)
                    ? $this->_oldAttributes[$name] : null;
            $this->_oldAttributes[$name] = $value;
        }
        $this->changedAttributes = $changedAttributes;
        $this->afterSave(false, $this->changedAttributes);
        return $rows;
    }


    public function searchByAttribute($attribute, $value, $is_strict = true, array $additional_criteria = []) {
        $query = self::find();
        if($is_strict) {
            $query->filterWhere([$attribute => $value]);
        }
        else {
            $query->filterWhere(['like', $attribute, $value]);
        }
        if(count($additional_criteria)) {
            foreach($additional_criteria as $criteria) {
                $query->andFilterWhere($criteria);
            }
        }
        return $query->all();
    }


    public function fields() {
        $fields = ArrayHelper::merge(parent::fields(), $this->safeAttributes());
        return $fields;
    }


    public function searchByIds(array $ids) {
        $query = self::find();
        $query->filterWhere(['id' => $ids]);
        return $query->all();
    }


    protected function initLocalTransaction() {
        if(!Yii::$app->db->getTransaction()) {
            $this->transaction = Yii::$app->db->beginTransaction();
        }
    }


    /**
     * Commits current local transaction
     */
    protected function commitLocalTransaction() {
        if($this->isLocalTransactionAccessible()) {
            $this->transaction->commit();
        }
    }


    /**
     * rollback current local transaction
     */
    protected function rollbackLocalTransaction() {
        if($this->isLocalTransactionAccessible()) {
            $this->transaction->rollBack();
        }
    }


    protected function isLocalTransactionAccessible() {
        $is_accessible = !is_null($this->transaction) && $this->transaction->isActive;
        return $is_accessible;
    }


    /** this might look unnecessary but it disables useless typecasting from ActiveRecord class
     * @param BaseActiveRecord $record
     * @param array $row
     */
    public static function populateRecord($record, $row) {
        BaseActiveRecord::populateRecord($record, $row);
    }


    public function save($runValidation = true, $attributeNames = null) {
        $this->initLocalTransaction();
        try {
            $is_saved = parent::save($runValidation, $attributeNames);
            if($this->is_saved === null) {
                $this->is_saved = $is_saved;
            }
        }
        catch(Exception $e) {
            Alert::addError($e->getMessage(),
                            ['class' => self::className(),
                             'line' => $e->getLine(),
                             'file' => $e->getFile(),
                             'trace' => $e->getTraceAsString(),
                             'id' => $this->id,
                             'isNewRecord' => $this->isNewRecord,
                             'errors' => $this->errors]);
            $this->rollbackLocalTransaction();
            $this->is_saved = false;
        }
        if($this->hasErrors() || count(Alert::getErrors()) || !$this->is_saved) {
            $this->rollbackLocalTransaction();
            $this->is_saved = false;
        }
        else {
            $this->commitLocalTransaction();
            $this->is_saved = true;
        }
        return $this->is_saved;
    }


    public function delete() {
        $this->initLocalTransaction();
        try {
            $is_deleted = parent::delete(); // TODO: Change the autogenerated stub
            if($this->is_deleted === null) {
                $this->is_deleted = $is_deleted;
            }
        }
        catch(Exception $e) {
            Alert::addError($e->getMessage(),
                            ['class' => self::className(),
                             'line' => $e->getLine(),
                             'file' => $e->getFile(),
                             'trace' => $e->getTraceAsString(),
                             'id' => $this->id,
                             'isNewRecord' => $this->isNewRecord,
                             'errors' => $this->errors]);
            $this->rollbackLocalTransaction();
            $this->is_deleted = false;
        }
        if($this->hasErrors() || count(Alert::getErrors()) || !$this->is_deleted) {
            $this->rollbackLocalTransaction();
            $this->is_deleted = false;
        }
        else {
            $this->commitLocalTransaction();
            $this->is_deleted = true;
        }
        return $this->is_deleted;
    }


    public static function convertDate($model, $attribute, $current_format, $desired_format) {
        if(!empty($model->$attribute)) {
            $buffer = $model->$attribute;
            $model->$attribute = DateTime::createFromFormat($current_format, $model->$attribute);
            if($model->$attribute instanceof DateTime) {
                $model->$attribute = $model->$attribute->format($desired_format);
            }
            else $model->$attribute = $buffer;
        }
    }


    public static function _formName() {
        /**
         * @var ActiveRecord $model
         */
        $reflector = new ReflectionClass(get_called_class());
        return $reflector->getShortName();
    }


    public function _className() {
        $class = get_called_class();
        return $class;
    }


    public static function getPrefixedAttribute($attribute) {
        return static::_formName() . '_' . $attribute;
    }


    public function unsetAttributes($names = null) {
        if($names === null) {
            $names = $this->attributes();
        }
        foreach($names as $name) {
            $this->$name = null;
        }
    }


    public function safeAttributesExcept($except = []) {
        $except = array_flip($except);
        $scenario = $this->getScenario();
        $scenarios = $this->scenarios();
        if(!isset($scenarios[$scenario])) {
            return [];
        }
        $attributes = [];
        foreach($scenarios[$scenario] as $attribute) {
            if(!isset($except[$attribute])) {
                if($attribute[0] !== '!') {
                    $attributes[] = $attribute;
                }
            }
        }
        return $attributes;
    }


    public static function asArray($key, $value) {
        $models = static::find()->all();
        $models = ArrayHelper::map($models, $key, $value);
        return $models;
    }


    public function ajaxValidate($key = null) {
    }
}