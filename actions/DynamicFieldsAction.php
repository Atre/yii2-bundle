<?php

namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\models\AParentActiveRecord;
use dezmont765\yii2bundle\models\ASubActiveRecord;
use dezmont765\yii2bundle\models\MainActiveRecord;
use Yii;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 08.05.2017
 * Time: 11:54
 */
abstract class DynamicFieldsAction extends MainAction
{
    const SUB_MODELS = 'sub_models';
    const CATEGORY = 'category';
    const SUB_MODEL_PARENT_CLASS = 'sub_model_parent_class';
    const BINDING_CLASS = 'binding_class';
    const SUB_MODEL_CLASS = 'sub_model_class';
    const CHILD_BINDING_ATTRIBUTE = 'child_binding_attribute';
    const PARENT_BINDING_ATTRIBUTE = 'parent_binding_attribute';

    public $model = null;


    abstract public function getModel($id);


    public function run($id = null) {
        $this->model = $this->getModel($id);
    }


    public function getSubModelClass($sub_model_class, $category, $sub_model_parent_class) {
        /**
         * @var $sub_model_parent_class AParentActiveRecord
         */
        $sub_model_class =
            $sub_model_class ?? $sub_model_parent_class::getSubTableClassByCategory($category);
        return $sub_model_class;
    }


    /**
     * @param array $sub_models
     * @param MainActiveRecord|string $sub_model_class
     */
    public function loadModelsFromRequest(&$sub_models = [], $sub_model_class) {
        if($sub_model_class !== null) {
            $sub_model_attribute_sets = Yii::$app->request->post($sub_model_class::_formName(), []);
            foreach($sub_model_attribute_sets as $key => $sub_model_attribute_set) {
                if(!isset($sub_models[$key]) || !$sub_models[$key] instanceof $sub_model_class) {
                    $sub_models[$key] = new $sub_model_class;
                }
                $sub_models[$key]->attributes = $sub_model_attribute_set;
            }
            if(empty($sub_models)) {
                $sub_models[] = new $sub_model_class;
            }
        }
    }


    public function getDefaultView() {
        return $this->controller->id . '-form';
    }


    /**
     * @param MainActiveRecord[] $sub_models
     * @param $child_binding_attribute
     * @param $parent_binding_attribute
     * @param $category
     */
    public function saveSubModels(&$sub_models, $category, $child_binding_attribute, $parent_binding_attribute) {
        foreach($sub_models as &$sub_model) {
            $sub_model->{$child_binding_attribute} =
                $this->model->{$parent_binding_attribute};
            $sub_model->category = $category;
            $sub_model->save();
        }
    }


    public function getBindingClass($binding_class, $sub_model_parent_class) {
        return $binding_class ?? $sub_model_parent_class;
    }


    /**
     * @param ASubActiveRecord|string $sub_model_class
     * @param MainActiveRecord|string $sub_model_parent_class
     * @param string $category
     * @param MainActiveRecord $binding_class
     * @param string $parent_binding_attribute
     * @param string $child_binding_attribute
     * @return ASubActiveRecord[]
     */
    public function findSubModels($sub_model_class, $sub_model_parent_class, $category, $binding_class, $child_binding_attribute, $parent_binding_attribute) {
        $sub_models = [];
        if($sub_model_class !== null) {
            if($sub_models !== null) {
                $boundary_attribute_value = $this->model->{$parent_binding_attribute};
                if($sub_model_class::tableName() !== $sub_model_parent_class::tableName()) {
                    $binding_class = $this->getBindingClass($binding_class, $sub_model_parent_class);
                    $sub_models = $sub_model_class::find()
                                                  ->joinWith($sub_model_class::getMainModelAttribute())
                                                  ->andWhere([$binding_class::tableName() . '.' .
                                                              $child_binding_attribute => $boundary_attribute_value])
                                                  ->andWhere(['category' => $category])
                                                  ->indexBy('id')
                                                  ->all();
                }
                else {
                    $sub_models =
                        $sub_model_class::find()->where(['AND',
                                                         [$child_binding_attribute => $boundary_attribute_value],
                                                         ['category' => $category]])
                                        ->indexBy('id')
                                        ->all();
                }
            }
        }
        return $sub_models;
    }


    abstract public function findExistingSubModels();

}