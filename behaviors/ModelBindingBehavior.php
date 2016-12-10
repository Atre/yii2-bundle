<?php
namespace dezmont765\yii2bundle\behaviors;

use dezmont765\yii2bundle\components\SafeArray;
use dezmont765\yii2bundle\models\MainActiveRecord;
use Yii;
use yii\base\Behavior;
use yii\base\Request;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * todo think about __get and __call. Will be awesome if I will be able to create dynamic methods
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 10.12.2016
 * Time: 21:20
 * @property MainActiveRecord $owner
 */
class ModelBindingBehavior extends Behavior
{

    public $binding_stores = [];


    public function init() {
        parent::init();
        $this->binding_stores = SafeArray::toSafe($this->binding_stores);
    }


    const BINDING_STORES = 'binding_stores';
    const BINDING_STORE_ATTRIBUTE = 'binding_store_attribute';
    const BINDING_MODEL_QUERY = 'binding_model_query';
    const BINDING_MODEL_CLASS = 'binding_model_class';
    const BINDING_ATTRIBUTE = 'binding_model_attribute';
    const BINDING_ATTRIBUTE_DELIMITER = 'binding_model_delimiter';
    const BINDING_INTERMEDIATE_MODEL_CLASS = 'binding_intermediate_model_class';
    const BINDING_INTERMEDIATE_MODEL_ATTRIBUTE = 'binding_intermediate_model_attribute';
    const BINDING_INTERMEDIATE_RELATED_ATTRIBUTE = 'binding_intermediate_related_attribute';


    public function events() {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }


    public function getMultipleBinding($attribute) {
        $store_attribute = $this->binding_stores[$attribute][self::BINDING_STORE_ATTRIBUTE];
        if($this->owner->$store_attribute !== null) {
            $result = implode($this->binding_stores[$attribute][self::BINDING_ATTRIBUTE_DELIMITER], $this->owner->$store_attribute);
            return $result;
        }
        else {
            $bound_models = [];
            $binding_model_query = $this->binding_stores[$attribute][self::BINDING_MODEL_QUERY];
            if($binding_model_query instanceof ActiveQuery) {
                $bound_models = $binding_model_query->all();
            }
            if(count($bound_models)) {
                $models_to_bind = [];
                foreach($bound_models as $bound_model) {
                    if($bound_model instanceof $this->binding_stores[$attribute][self::BINDING_MODEL_CLASS]) {
                        $models_to_bind[] =
                            $bound_model->{$this->binding_stores[$attribute][self::BINDING_ATTRIBUTE]};
                    }
                }
                $result =
                    implode($this->binding_stores[$attribute][self::BINDING_ATTRIBUTE_DELIMITER], $models_to_bind);
                return $result;
            }
            else {
                return null;
            }
        }
    }


    public function getSingleBinding($attribute) {
        $store_attribute = $this->binding_stores[$attribute][self::BINDING_STORE_ATTRIBUTE];
        if($this->owner->$store_attribute !== null) {
            $result = $this->owner->$store_attribute;
            return $result;
        }
        else {
            $bound_model = null;
            $binding_model_query = $this->binding_stores[$attribute][self::BINDING_MODEL_QUERY];
            if($binding_model_query instanceof ActiveQuery) {
                $bound_model = $binding_model_query->one();
            }
            if($bound_model instanceof $this->binding_stores[$attribute][self::BINDING_MODEL_CLASS]) {
                $result = $bound_model->{$this->binding_stores[$attribute][self::BINDING_ATTRIBUTE]};
                return $result;
            }
            else {
                return '';
            }
        }
    }


    public function saveBinding($attribute, $binding_store) {
        $form_name = $this->owner->formName();
        if(Yii::$app->request instanceof Request && !empty(Yii::$app->request->getBodyParam($form_name))) {
            if(!empty($this->owner->$attribute)) {
                /**@var MainActiveRecord $intermediate_model_class */
                /**@var MainActiveRecord $owner_model_class */
                $intermediate_model_class = $binding_store[self::BINDING_INTERMEDIATE_MODEL_CLASS];
                //todo solve ID problem, it has to deal with combined id's
                $intermediate_model_class::deleteAll([$binding_store[self::BINDING_INTERMEDIATE_MODEL_ATTRIBUTE] => $this->owner->id]);
                /**@var MainActiveRecord $model_class */
                $model_class = $binding_store[self::BINDING_MODEL_CLASS];
                $store_attribute = $binding_store[self::BINDING_STORE_ATTRIBUTE];
                if(!is_array($this->owner->$store_attribute)) {
                    $store_attribute_value = [$this->owner->$store_attribute];
                }
                else {
                    $store_attribute_value = $this->owner->$store_attribute;
                }
                $models = $model_class::find()
                                      ->where(['in', 'id', $store_attribute_value])->all();
                foreach($models as $model) {
                    if($model instanceof $model_class) {
                        /** @var MainActiveRecord $model_binding */
                        $model_binding = new $intermediate_model_class();
                        //todo solve ID problem, it has to deal with combined id's
                        $model_binding->{$binding_store[self::BINDING_INTERMEDIATE_MODEL_ATTRIBUTE]} =
                            $this->owner->id;
                        $model_binding->{$binding_store[self::BINDING_INTERMEDIATE_RELATED_ATTRIBUTE]} =
                            $model->id;
                        $model_binding->save();
                    }
                }
            }
        }
    }


    public function afterSave() {
        foreach($this->binding_stores as $attribute => $binding_store) {
            $this->saveBinding($attribute, $binding_store);
        }
    }
}