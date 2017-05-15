<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\components\geometry\IllegalArgumentException;
use dezmont765\yii2bundle\models\AParentActiveRecord;
use dezmont765\yii2bundle\models\MainActiveRecord;
use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 15.05.2017
 * Time: 13:40
 * @property AParentActiveRecord $child_models_parent_class
 * @property \dezmont765\yii2bundle\models\ASubActiveRecord $child_models_sub_class
 * @property \dezmont765\yii2bundle\models\ASubActiveRecord $child_models_basic_class
 * @property \dezmont765\yii2bundle\models\ASubActiveRecord[] | [] $child_models
 */
class DynamicFieldsActionFieldsBundle extends Object
{
    public $child_models = [];
    public $category = null;
    public $child_models_parent_class = null;
    public $child_models_sub_class = null;
    public $child_binding_attribute = null;
    public $parent_binding_attribute = null;
    public $category_get_strategy = null;
    public $category_post_param = 'category';
    public $child_models_search_strategy = null;
    public $category_attribute = 'category';
    public $child_models_basic_class = null;

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
    const CATEGORY_ATTRIBUTE = 'category_attribute';


    public function initialValues() {
        return [
            self::CHILD_MODELS => [],
            self::CHILD_MODELS_SUB_CLASS => null,
            self::CATEGORY_POST_PARAM => 'category',
            self::CATEGORY_GET_STRATEGY => null,
            self::CATEGORY_ATTRIBUTE => 'category',
        ];
    }


    /**
     * DynamicFieldsActionFieldsBundle constructor.
     * @param array $config
     * @internal param AParentActiveRecord $child_models_parent_class
     * @internal param $child_binding_attribute
     * @internal param $parent_binding_attribute
     * @internal param $child_models_search_strategy
     * @internal param null $child_models_sub_class
     * @internal param null $category_get_strategy
     * @internal param string $category_post_param
     * @internal param string $category_attribute
     */
    public function __construct($config) {
        $config = ArrayHelper::merge($this->initialValues(), $config);
        parent::__construct($config);
        $this->setCategory();
        $this->setChildModelsSubClass($config[self::CHILD_MODELS_SUB_CLASS]);
        $child_models_parent_class = $this->child_models_parent_class;
        $this->child_models_basic_class = $child_models_parent_class::basicSubTablesClass();;
    }


    private function setCategory() {
        $child_models_parent_class = $this->child_models_parent_class;
        $post = Yii::$app->request->getBodyParam($child_models_parent_class::_formName(), []);
        $category = isset($post[$this->category_post_param]) ? $post[$this->category_post_param] : null;
        if(is_callable($this->category_get_strategy)) {
            $this->category = call_user_func_array($this->category_get_strategy, $this->category);
        }
        $this->category = $category;
    }


    private function setChildModelsSubClass($child_models_sub_class) {
        if($this->category) {
            $child_models_parent_class = $this->child_models_parent_class;
            $this->child_models_sub_class =
                $child_models_sub_class ?? $child_models_parent_class::getSubTableClassByCategory($this->category);
        }
    }


    public function loadModelsFromRequest() {
        if($this->child_models_sub_class !== null) {
            $child_models_sub_class = $this->child_models_sub_class;
            $child_models_data = Yii::$app->request->post($child_models_sub_class::_formName(), []);
            foreach($child_models_data as $key => $child_models_attributes_set) {
                if(!isset($this->child_models[$key]) || !$this->child_models[$key] instanceof $child_models_sub_class) {
                    $this->child_models[$key] = new $child_models_sub_class;
                }
                $this->child_models[$key]->attributes = $child_models_attributes_set;
            }
            if(empty($this->child_models)) {
                $this->child_models[] = new $child_models_sub_class;
            }
        }
    }


    public function saveChildModels($parent_model) {
        foreach($this->child_models as &$child_model) {
            $child_model->{$this->child_binding_attribute} = $parent_model->{$this->parent_binding_attribute};
            $child_model->{$this->category_attribute} = $this->category;
            $child_model->save();
        }
    }


    public function findChildModels($parent_model) {
        switch($this->child_models_search_strategy) {
            case DynamicFieldsAction::FIND_CHILD_SUB_MODELS :
                $this->findChildSubModels($parent_model);
                break;
            case DynamicFieldsAction::FIND_CHILD_SUB_MODELS_VIA_PARENT_CLASS_RELATION :
                $this->findChildSubModelsViaParentClassRelation($parent_model);
                break;
            case DynamicFieldsAction::FIND_CHILD_PARENT_MODELS :
                $this->findChildParentModels($parent_model);
                break;
            case DynamicFieldsAction::FIND_CHILD_PARENT_MODELS_VIA_SUB_CLASS_RELATION :
                $this->findChildParentModelsViaSubClassRelation($parent_model);
                break;
            default:
                throw new IllegalArgumentException('Please define child models search strategy');
                break;
        }
    }


    /**
     * @param MainActiveRecord $search_class
     * @param $parent_model
     * @param null $relation
     * @param MainActiveRecord|null $binding_class
     * @return array|\yii\db\ActiveRecord[]
     */
    private function findChildModelsInternal($search_class, $parent_model, $relation = null, $binding_class = null) {

        if($search_class !== null) {
            $parent_binding_value = $parent_model->{$this->parent_binding_attribute};
            $child_models_query = $search_class::find();
            $child_models_basic_class = $this->child_models_basic_class;
            $check_is_basic = $child_models_basic_class !== null;
            $is_basic = $check_is_basic ? $search_class::tableName() !== $child_models_basic_class::tableName() : true;
            if($relation !== null && $is_basic) {
                $child_models_query->joinWith($relation)
                                   ->andWhere([$binding_class::tableName() . '.' .
                                               $this->child_binding_attribute => $parent_binding_value]);
            }
            else {
                $child_models_query->where([$this->child_binding_attribute => $parent_binding_value]);
            }
            $this->child_models = $child_models_query->andWhere(['category' => $this->category])->indexBy('id')->all();
        }
    }


    private function findChildSubModels($parent_model) {
        $child_models_sub_class = $this->child_models_sub_class;
        if($child_models_sub_class !== null) {
            $relation = $child_models_sub_class::getMainModelAttribute();
            $this->findChildModelsInternal($child_models_sub_class, $parent_model, $relation,
                                                  $child_models_sub_class);
        }
    }


    private function findChildSubModelsViaParentClassRelation($parent_model) {
        $child_models_sub_class = $this->child_models_sub_class;
        if($child_models_sub_class !== null) {
            $relation = $child_models_sub_class::getMainModelAttribute();
            $this->findChildModelsInternal($child_models_sub_class,
                                                  $parent_model,
                                                  $relation,
                                                  $this->child_models_sub_class);
        }

    }


    private function findChildParentModels($parent_model) {
        $this->findChildModelsInternal($this->child_models_parent_class, $parent_model);
    }


    /**
     * @param $parent_model
     * @internal param AParentActiveRecord|string $child_model_class
     */
    private function findChildParentModelsViaSubClassRelation($parent_model) {
        $child_models_parent_class = $this->child_models_parent_class;
        $relation = $child_models_parent_class::getSubTableRelationFieldByCategory($this->category);
        $this->findChildModelsInternal($child_models_parent_class, $parent_model, $relation,
                                              $this->child_models_sub_class);
    }


}