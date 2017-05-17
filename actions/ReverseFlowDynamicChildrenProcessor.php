<?php
namespace dezmont765\yii2bundle\actions;

use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.05.2017
 * Time: 11:41
 */
class ReverseFlowDynamicChildrenProcessor extends DynamicChildrenProcessor
{
    public function loadChildModelsFromRequest() {
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


    protected function findChildModelsViaSubRelation($parent_model) {
        $child_models_sub_class = $this->child_models_sub_class;
        if($child_models_sub_class !== null) {
            $relation = $child_models_sub_class::getConnectedModelAttribute();
            $this->findChildModelsInternal($child_models_sub_class, $parent_model, $relation,
                                           $child_models_sub_class);
        }
    }


    protected function findChildModelsViaMainRelation($parent_model) {
        $child_models_sub_class = $this->child_models_sub_class;
        if($child_models_sub_class !== null) {
            $relation = $child_models_sub_class::getConnectedModelAttribute();
            $this->findChildModelsInternal($child_models_sub_class,
                                           $parent_model,
                                           $relation,
                                           $this->child_models_parent_class);
        }
    }
}