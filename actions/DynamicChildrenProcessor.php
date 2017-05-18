<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\components\geometry\IllegalArgumentException;
use dezmont765\yii2bundle\events\DynamicChildrenAfterDataLoadEvent;
use dezmont765\yii2bundle\models\AExtendableActiveRecord;
use dezmont765\yii2bundle\models\MainActiveRecord;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 15.05.2017
 * Time: 13:40
 * @property AExtendableActiveRecord $child_models_parent_class
 * @property \dezmont765\yii2bundle\models\ADependentActiveRecord $child_models_sub_class
 * @property \dezmont765\yii2bundle\models\ADependentActiveRecord $child_models_basic_class
 * @property \dezmont765\yii2bundle\models\ADependentActiveRecord[] $child_models
 */
abstract class DynamicChildrenProcessor extends Object implements IDynamicChildrenProcessor
{
    const AFTER_LOAD_CHILD_MODELS_EVENT = 'after_load_child_models_event';
    public $child_models = [];
    public $category = null;
    public $child_models_parent_class = null;
    public $child_models_sub_class = null;
    public $child_binding_attribute = null;
    public $parent_binding_attribute = null;
    public $category_get_strategy = null;
    public $category_post_param = 'category';
    public $child_models_binding_strategy = null;
    public $category_attribute = 'category';
    public $child_models_basic_class = null;
    public $relation_strategy = self::VIA_MAIN_RELATION;
    public $child_models_data = [];

    const CHILD_MODELS = 'child_models';
    const CATEGORY = 'category';
    const CHILD_MODELS_PARENT_CLASS = 'child_models_parent_class';
    const BINDING_CLASS = 'binding_class';
    const CHILD_MODELS_SUB_CLASS = 'child_models_sub_class';
    const CHILD_BINDING_ATTRIBUTE = 'child_binding_attribute';
    const PARENT_BINDING_ATTRIBUTE = 'parent_binding_attribute';
    const CATEGORY_GET_STRATEGY = 'category_get_strategy';
    const CATEGORY_POST_PARAM = 'category_post_param';
    const CHILD_MODELS_BINDING_STRATEGY = 'child_models_binding_strategy';
    const CATEGORY_ATTRIBUTE = 'category_attribute';

    const VIA_SUB_RELATION = 'sub-relation';
    const VIA_MAIN_RELATION = 'main-relation';

    const FIND_CHILD_SUB_MODELS = 'findChildSubModels';
    const FIND_CHILD_SUB_MODELS_VIA_PARENT_CLASS_RELATION = 'findChildSubModelsViaParentClassRelation';
    const FIND_CHILD_PARENT_MODELS_VIA_SUB_CLASS_RELATION = 'findChildParentModelsViaSubClassRelation';
    const FIND_CHILD_PARENT_MODELS = 'findChildParentModels';


    public function afterLoadChildModels() {
        Event::trigger(get_called_class(), self::AFTER_LOAD_CHILD_MODELS_EVENT,
                       new DynamicChildrenAfterDataLoadEvent($this));
        return true;
    }


    private function initialValues() {
        return [
            self::CHILD_MODELS => [],
            self::CHILD_MODELS_SUB_CLASS => null,
            self::CATEGORY_POST_PARAM => 'category',
            self::CATEGORY_GET_STRATEGY => null,
            self::CATEGORY_ATTRIBUTE => 'category',
        ];
    }


    /**
     * DynamicChildrenProcessor constructor.
     * @param array $config
     * @internal param AExtendableActiveRecord $child_models_parent_class
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
        $this->setChildBindingAttribute();
        $child_models_parent_class = $this->child_models_parent_class;
        $this->child_models_basic_class = $child_models_parent_class::dummySubTablesClass();;
    }


    private function setCategory() {
        $child_models_parent_class = $this->child_models_parent_class;
        $categories = Yii::$app->request->getBodyParam('categories', []);
        $category = isset($categories[$child_models_parent_class::_formName()]['category']) ?
            $categories[$child_models_parent_class::_formName()]['category'] : null;
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


    public function saveChildModels($parent_model) {
        foreach($this->child_models as &$child_model) {
            $child_model->{$this->child_binding_attribute} = $parent_model->{$this->parent_binding_attribute};
            $child_model->{$this->category_attribute} = $this->category;
            $child_model->save();
        }
    }


    abstract protected function findChildModelsViaSubRelation($parent_model);


    abstract protected function findChildModelsViaMainRelation($parent_model);


    public function findChildModels($parent_model) {
        switch($this->child_models_binding_strategy) {
            case self::VIA_MAIN_RELATION :
                $this->findChildModelsViaMainRelation($parent_model);
                break;
            case self::VIA_SUB_RELATION :
                $this->findChildModelsViaSubRelation($parent_model);
                break;
            default:
                throw new InvalidConfigException('Please define child models search strategy');
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
    protected function findChildModelsInternal($search_class, $parent_model, $relation = null, $binding_class = null) {
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


    private function setChildBindingAttribute() {
        if($this->child_binding_attribute === null) {
            $child_models_sub_class = $this->child_models_sub_class;
            if($child_models_sub_class !== null) {
                $this->child_binding_attribute = $child_models_sub_class::getParentBindingAttribute();
            }
        }
    }


}