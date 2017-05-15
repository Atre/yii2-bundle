<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\components\geometry\IllegalArgumentException;
use dezmont765\yii2bundle\models\AParentActiveRecord;
use dezmont765\yii2bundle\models\ASubActiveRecord;
use dezmont765\yii2bundle\models\MainActiveRecord;
use dezmont765\yii2bundle\widgets\PartialActiveForm;
use Yii;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 08.05.2017
 * Time: 11:54
 * @property MainActiveRecord $model
 */
abstract class DynamicFieldsAction extends MainAction
{
    const CHILD_MODELS = 'child_models';
    const CATEGORY = 'category';
    const CHILD_MODELS_PARENT_CLASS = 'child_models_parent_class';
    const BINDING_CLASS = 'binding_class';
    const CHILD_MODELS_SUB_CLASS = 'child_models_sub_class';
    const CHILD_BINDING_ATTRIBUTE = 'child_binding_attribute';
    const PARENT_BINDING_ATTRIBUTE = 'parent_binding_attribute';
    const CATEGORY_GET_STRATEGY = 'category_get_strategy';
    const CATEGORY_POST_PARAM = 'category_post_param';
    const CHILD_MODELS_SEARCH_STRATEGY = 'child_models_search_strategy';


    const FIND_CHILD_SUB_MODELS = 'findChildSubModels';
    const FIND_CHILD_SUB_MODELS_VIA_PARENT_CLASS_RELATION = 'findChildSubModelsViaParentClassRelation';
    const FIND_CHILD_PARENT_MODELS_VIA_SUB_CLASS_RELATION = 'findChildParentModelsViaSubClassRelation';
    const FIND_CHILD_PARENT_MODELS = 'findChildParentModels';

    public $model = null;


    public function getCategory($set_category_strategy, $category) {
        if(is_callable($set_category_strategy)) {
            return call_user_func_array($set_category_strategy, [$category]);
        }
        else return $category;
    }


    abstract public function getModel($id);


    public function run($id = null) {
        $this->model = $this->getModel($id);
        $this->model->load(Yii::$app->request->post());
    }


    public function getChildModelsSubClass($sub_model_class, $category, $sub_model_parent_class) {
        /**
         * @var $sub_model_parent_class AParentActiveRecord
         */
        $sub_model_class =
            $sub_model_class ?? $sub_model_parent_class::getSubTableClassByCategory($category);
        return $sub_model_class;
    }


    /**
     * @param array $sub_models
     * @param MainActiveRecord|string $child_model_sub_class
     */
    public function loadModelsFromRequest(&$sub_models = [], $child_model_sub_class) {
        if($child_model_sub_class !== null) {
            $sub_model_attribute_sets = Yii::$app->request->post($child_model_sub_class::_formName(), []);
            foreach($sub_model_attribute_sets as $key => $sub_model_attribute_set) {
                if(!isset($sub_models[$key]) || !$sub_models[$key] instanceof $child_model_sub_class) {
                    $sub_models[$key] = new $child_model_sub_class;
                }
                $sub_models[$key]->attributes = $sub_model_attribute_set;
            }
            if(empty($sub_models)) {
                $sub_models[] = new $child_model_sub_class;
            }
        }
    }


//    /**
//     * @param $child_models
//     * @param MainActiveRecord $child_model_parent_class
//     */
//    public function loadChildModelsViaParentClass(&$child_models, $child_model_sub_class, $child_model_parent_class) {
//        if($child_model_parent_class !== null) {
//            $child_model_data_sets = Yii::$app->request->post($child_model_parent_class::_formName(), []);
//            foreach($child_model_data_sets as $key => $child_model_data_set) {
//                if(!isset($child_models[$key]) || !$child_models[$key] instanceof $child_model_parent_class) {
//                    $child_models[$key] = new $child_model_parent_class;
//                }
//                $child_models[$key]->attributes = $child_model_data_set;
//            }
//            if(empty($child_models)) {
//                $child_models[] = new $child_model_parent_class;
//            }
//            foreach($child_models as $child_model) {
//                if($child_model instanceof AParentActiveRecord) {
//                    $child_model->set
//                }
//            }
//        }
//    }


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
     * @param $search_strategy
     * @param ASubActiveRecord|string $child_model_sub_class
     * @param AParentActiveRecord|string $child_model_parent_class
     * @param $category
     * @param $child_binding_attribute
     * @param $parent_binding_attribute
     * @return array|ASubActiveRecord[]|\yii\db\ActiveRecord[]
     * @throws IllegalArgumentException
     */
    public function findChildModels($search_strategy, $child_model_sub_class, $child_model_parent_class, $category,
                                    $child_binding_attribute, $parent_binding_attribute) {
        $child_model_basic_class = $child_model_parent_class::basicSubTablesClass();
        switch($search_strategy) {
            case self::FIND_CHILD_SUB_MODELS :
                return $this->findChildSubModels($child_model_sub_class, $category, $child_binding_attribute,
                                                 $parent_binding_attribute, $child_model_basic_class);
            case self::FIND_CHILD_SUB_MODELS_VIA_PARENT_CLASS_RELATION :
                return $this->findChildSubModelsViaParentClassRelation($child_model_sub_class,
                                                                       $child_model_parent_class,
                                                                       $category, $child_binding_attribute,
                                                                       $parent_binding_attribute,
                                                                       $child_model_basic_class);
            case self::FIND_CHILD_PARENT_MODELS :
                return $this->findChildParentModels($child_model_parent_class, $category, $child_binding_attribute,
                                                    $parent_binding_attribute, $child_model_basic_class);
            case self::FIND_CHILD_PARENT_MODELS_VIA_SUB_CLASS_RELATION :
                return $this->findChildParentModelsViaSubClassRelation($child_model_sub_class,
                                                                       $child_model_parent_class,
                                                                       $category, $child_model_parent_class,
                                                                       $parent_binding_attribute,
                                                                       $child_model_basic_class);
            default:
                throw new IllegalArgumentException('Please define child models search strategy');
                break;
        }
    }


    /**
     * @param MainActiveRecord $search_class
     * @param $category
     * @param $child_binding_attribute
     * @param $parent_binding_attribute
     * @param null $relation
     * @param MainActiveRecord|null $binding_class
     * @param null $child_model_basic_class
     * @return array|\yii\db\ActiveRecord[]
     */
    private function findChildModelsInternal($search_class, $category, $child_binding_attribute,
                                             $parent_binding_attribute, $relation = null, $binding_class = null,
                                             $child_model_basic_class = null) {
        $child_models = [];
        if($search_class !== null) {
            $parent_binding_value = $this->model->{$parent_binding_attribute};
            $child_models_query = $search_class::find();
            $check_is_basic = $child_model_basic_class !== null;
            $is_basic = $check_is_basic ? $search_class::tableName() !== $child_model_basic_class::tableName() : true;
            if($relation !== null && $is_basic) {
                $child_models_query->joinWith($relation)
                                   ->andWhere([$binding_class::tableName() . '.' .
                                               $child_binding_attribute => $parent_binding_value]);
            }
            else {
                $child_models_query->where([$child_binding_attribute => $parent_binding_value]);
            }
            $child_models = $child_models_query->andWhere(['category' => $category])->indexBy('id')->all();
        }
        return $child_models;
    }


    /**
     * @param ASubActiveRecord|string $child_model_sub_class
     * @param string $category
     * @param string $child_binding_attribute
     * @param string $parent_binding_attribute
     * @param null $child_model_basic_class
     * @return ASubActiveRecord[]
     */
    private function findChildSubModels($child_model_sub_class, $category, $child_binding_attribute,
                                        $parent_binding_attribute, $child_model_basic_class = null) {
        if($child_model_sub_class !== null) {
            $relation = $child_model_sub_class::getMainModelAttribute();
            return $this->findChildModelsInternal($child_model_sub_class, $category, $child_binding_attribute,
                                                  $parent_binding_attribute, $relation, $child_model_sub_class,
                                                  $child_model_basic_class);
        }
        return [];
    }


    /**
     * @param ASubActiveRecord|string $child_model_sub_class
     * @param AParentActiveRecord|string $child_model_parent_class
     * @param $category
     * @param $child_binding_attribute
     * @param $parent_binding_attribute
     * @param null $child_model_basic_class
     * @return array
     */
    private function findChildSubModelsViaParentClassRelation($child_model_sub_class, $child_model_parent_class,
                                                              $category, $child_binding_attribute,
                                                              $parent_binding_attribute,
                                                              $child_model_basic_class = null) {
        if($child_model_sub_class !== null) {
            $relation = $child_model_sub_class::getMainModelAttribute();
            return $this->findChildModelsInternal($child_model_sub_class, $category, $child_binding_attribute,
                                                  $parent_binding_attribute,
                                                  $relation,
                                                  $child_model_parent_class, $child_model_basic_class);
        }
        return [];
    }


    /**
     * @param AParentActiveRecord|string $child_model_parent_class
     * @param $category
     * @param $child_binding_attribute
     * @param $parent_binding_attribute
     * @param null $child_model_basic_class
     * @return array
     */
    private function findChildParentModels($child_model_parent_class, $category, $child_binding_attribute,
                                           $parent_binding_attribute, $child_model_basic_class = null) {
        return $this->findChildModelsInternal($child_model_parent_class, $category, $child_binding_attribute,
                                              $parent_binding_attribute, $child_model_basic_class);
    }


    /**
     * @param ASubActiveRecord|string $child_model_sub_class
     * @param AParentActiveRecord|string $child_model_parent_class
     * @param $category
     * @param $child_binding_attribute
     * @param $parent_binding_attribute
     * @param null $child_model_basic_class
     * @return array|\yii\db\ActiveRecord[]
     * @internal param AParentActiveRecord|string $child_model_class
     */
    private function findChildParentModelsViaSubClassRelation($child_model_sub_class, $child_model_parent_class,
                                                              $category, $child_binding_attribute,
                                                              $parent_binding_attribute,
                                                              $child_model_basic_class = null) {
        $relation = $child_model_parent_class::getSubTableRelationFieldByCategory($category);
        return $this->findChildModelsInternal($child_model_parent_class, $category, $child_binding_attribute,
                                              $parent_binding_attribute, $relation, $child_model_sub_class,
                                              $child_model_basic_class);
    }


    abstract public function findExistingSubModels();

}