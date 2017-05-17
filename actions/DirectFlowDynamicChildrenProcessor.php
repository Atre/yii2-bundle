<?php
namespace dezmont765\yii2bundle\actions;

use Yii;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.05.2017
 * Time: 12:09
 * @property \dezmont765\yii2bundle\models\AExtendableActiveRecord[] $child_models
 */
class DirectFlowDynamicChildrenProcessor extends DynamicChildrenProcessor
{

    protected function findChildModelsViaSubRelation($parent_model) {
        $child_models_parent_class = $this->child_models_parent_class;
        if($this->category) {
            $relation = $child_models_parent_class::getSubTableRelationFieldByCategory($this->category);
            $this->findChildModelsInternal($child_models_parent_class, $parent_model, $relation,
                                           $this->child_models_sub_class);
        }
    }


    protected function findChildModelsViaMainRelation($parent_model) {
        $this->findChildModelsInternal($this->child_models_parent_class, $parent_model);
    }


    public function saveChildModels($parent_model) {
        foreach($this->child_models as &$child_model) {
            $child_model->subModel->{$this->child_binding_attribute} = $parent_model->{$this->parent_binding_attribute};
            $child_model->save();
        }
    }


    public function loadChildModelsFromRequest() {
        if($this->child_models_parent_class !== null && $this->child_models_sub_class !== null) {
            $child_models_parent_class = $this->child_models_parent_class;
            $child_models_sub_class = $this->child_models_sub_class;
            $child_models_parent_data = Yii::$app->request->post($child_models_parent_class::_formName(), []);
            $child_models_sub_data = Yii::$app->request->post($child_models_sub_class::_formName(), []);
            foreach($child_models_parent_data as $key => $child_models_attributes_set) {
                if(!isset($this->child_models[$key]) ||
                   !$this->child_models[$key] instanceof $child_models_parent_class
                ) {
                    $this->child_models[$key] = new $child_models_parent_class;
                }
                $this->child_models[$key]->attributes = $child_models_attributes_set;
                $this->child_models[$key]->category = $this->category;
                if(isset($child_models_sub_data[$key])) {
                    $this->child_models[$key]->subModel->load($child_models_sub_data[$key]);
                }
            }
            if(empty($this->child_models)) {
                $this->child_models[] = new $child_models_parent_class;
            }
        }
    }


}