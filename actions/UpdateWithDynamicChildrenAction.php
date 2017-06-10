<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\events\DynamicChildrenAfterDataLoadEvent;
use dezmont765\yii2bundle\models\MainActiveRecord;
use yii\base\Event;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 30.04.2017
 * Time: 21:22
 */
class UpdateWithDynamicChildrenAction extends DynamicChildrenAction
{

    public function init() {
        parent::init();
        Event::on(ReverseFlowDynamicChildrenProcessor::className(),
                  DynamicChildrenProcessor::AFTER_LOAD_CHILD_MODELS_EVENT, [$this, 'transform']);
        Event::on(DirectFlowDynamicChildrenProcessor::className(),
                  DynamicChildrenProcessor::AFTER_LOAD_CHILD_MODELS_EVENT, [$this, 'transform']);
    }


    public function transform(DynamicChildrenAfterDataLoadEvent $event) {
        if(!empty($event->field_processor->child_models_data)) {
            /** @var MainActiveRecord $models_to_delete */
            $models_to_delete =
                array_diff_key($event->field_processor->child_models, $event->field_processor->child_models_data);
            foreach($models_to_delete as $key => $model_to_delete) {
                $model_to_delete->delete();
                unset($event->field_processor->child_models[$key]);
            }
        }
    }


    public function getModel($id = null) {
        $this->model_class = $this->getModelClass();
        return $this->controller->findModel($this->model_class, $id);
    }


    public function run() {
        $this->findChildModels();
        $this->loadChildModelsFromRequest();
        $result = $this->save();
        if($result !== null) {
            return $result;
        }
        return $this->controller->render($this->getView(), ['model' => $this->model]);
    }
}