<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\behaviors\SaveDependentModelsBehavior;
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
 *
 * @property \dezmont765\yii2bundle\models\ADependentActiveRecord[] $child_models
 * This class contains the main logic of processing any kind of children, saved with their parent within single form.
 * It happens, that we need to split some entity into the group of entities, which have few common attributes and few
 *     different. In that case very useful to have them stored in form of one "extendable" table(which contains all
 *     common attributes and a special "category" field), and several additional "dependent" tables bound via
 *     one-to-one relation. This technique is very similar to OOP inheritance.
 * There are 2 options of building that :
 * 1) "Direct" flow
 *     In this option "dependent" model is stored inside "extendable" one within "subModel" field.
 * @see DirectFlowDynamicChildrenProcessor. It loads all extendable and dependent models separately, and then using
 * @see SaveDependentModelsBehavior saves them within saving cycle.
 * 2) "Reverse" flow
 *     In this option we have a special virtual forms, which inherit from the one main "dependent" model. This model
 *     contains duplicates of all common fields to allow our virtual form validate and process them. Each of these
 *     forms is connected to it's own "dependent" table, thus the "extendable" model should be saved before them.
 *     So that's why we call this option "reverse": we save the "extendable" model withing saving cycle of "dependent"
 *     form.
 * @see ReverseFlowDynamicChildrenProcessor
 */
abstract class DynamicChildrenProcessor extends Object implements IDynamicChildrenProcessor
{

    public $child_models = [];
    public $category = null;
    /**
     * @var AExtendableActiveRecord|string $child_models_parent_class
     * This is "extendable" models class string
     * @see DynamicChildrenProcessor to get the idea what this attribute is responsible for
     */
    public $child_models_parent_class = null;
    /**
     * @var \dezmont765\yii2bundle\models\ADependentActiveRecord $child_models_sub_class
     * This is "dependent" models class string
     * @see DynamicChildrenProcessor to get the idea what this attribute is responsible for
     */
    public $child_models_sub_class = null;
    /**
     * Name of the attribute which binds a "child" model with a "parent" model
     * @var null
     */
    public $child_binding_attribute = null;
    /**
     * Name of the attribute which binds a "parent" model with a "child" model
     * @var null
     */
    public $parent_binding_attribute = null;
    /**
     * @var callable $category_get_strategy
     * Determines the process of getting the "category".
     */
    public $category_get_strategy = null;
    /**
     * The name of the post parameter where the "category" is stored
     * @var string
     */
    public $category_post_param = 'category';
    /**
     * There are 2 options of binding the "parent" and "child" models together.
     * 1) When the binding is between the "parent" model and the "extendable" model. This is also applied when
     * there are no complex "extendable" - "dependent" relation and we have just simple children.
     * @see DynamicChildrenProcessor::VIA_MAIN_RELATION
     * 2) When the binding is between the "parent" model and the "dependent" model. This is a more complex one, when we
     * bind to the "extendable" model through the "dependent"
     * @see DynamicChildrenProcessor::VIA_SUB_RELATION
     * @var string $child_models_binding_strategy
     */
    public $child_models_binding_strategy = null;
    /**
     * The name of the db field where the category info is stored
     * @var string
     */
    public $category_attribute = 'category';
    /**
     * It happens sometime in case of "reverse" flow, that one of "dependent" model doesn't have it's own table. This
     * case needs to be processed differently, so the class should be determined explicitly
     * @var \dezmont765\yii2bundle\models\ADependentActiveRecord|string
     */
    public $child_models_basic_class = null;
    public $relation_strategy = self::VIA_MAIN_RELATION;
    public $child_models_data = [];

    /**
     * Name of the event, which happens after gathering children related data from the source (currently only from
     * request)
     */
    const AFTER_LOAD_CHILD_MODELS_EVENT = 'after_load_child_models_event';
    /**
     * Constants block begin
     * All constants described below are made just for convenience. They duplicate the attributes names.
     */
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
    /** Constants block end */
    /**
     * @return bool
     */
    /**
     * Happens after gathering children related data from the source.
     * @return bool
     */
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


    /**
     * Sets the current category, trying to get it from different sources.
     * 1) From $_POST['categories'] by child_models_parent_class. @see $child_models_parent_class
     * 2) Using the callback
     */
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


    /**
     * Sets the class of "dependent" model directly or by category
     * @param $child_models_sub_class
     */
    private function setChildModelsSubClass($child_models_sub_class) {
        if($this->category) {
            $child_models_parent_class = $this->child_models_parent_class;
            $this->child_models_sub_class =
                $child_models_sub_class ?? $child_models_parent_class::getSubTableClassByCategory($this->category);
        }
    }


    /**
     * Saves all children, sets their binding attribute. E.g children.parent_id = parent.id
     * @param $parent_model
     */
    public function saveChildModels($parent_model) {
        foreach($this->child_models as &$child_model) {
            $child_model->{$this->child_binding_attribute} = $parent_model->{$this->parent_binding_attribute};
            $child_model->{$this->category_attribute} = $this->category;
            $child_model->save();
        }
    }


    /**
     * @see $child_models_search_strategy
     * @param $parent_model
     * @return mixed
     */
    abstract protected function findChildModelsViaSubRelation($parent_model);


    /**
     * @see $child_models_binding_strategy
     * @param $parent_model
     * @return mixed
     */
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
     * Performs search for children
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


    /**
     * Sets the binding from children to parent directly or via "dependent" model class
     */
    private function setChildBindingAttribute() {
        if($this->child_binding_attribute === null) {
            $child_models_sub_class = $this->child_models_sub_class;
            if($child_models_sub_class !== null) {
                $this->child_binding_attribute = $child_models_sub_class::getParentBindingAttribute();
            }
        }
    }


}