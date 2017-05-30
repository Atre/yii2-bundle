<?php
namespace dezmont765\yii2bundle\actions;

use dezmont765\yii2bundle\components\geometry\IllegalArgumentException;
use dezmont765\yii2bundle\models\AExtendableActiveRecord;
use dezmont765\yii2bundle\models\ADependentActiveRecord;
use dezmont765\yii2bundle\models\MainActiveRecord;
use dezmont765\yii2bundle\widgets\PartialActiveForm;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 08.05.2017
 * Time: 11:54
 * @property MainActiveRecord $model . This is a "parent" model .
 * @property AExtendableActiveRecord $sub_model_parent_class
 * @property ADependentActiveRecord $sub_model_class
 * @property DynamicChildrenProcessor[] | array $fields
 * @property DynamicChildrenProcessor|string $fields_processor
 * This action allows you to process the model with it's children using the only one form
 */
abstract class DynamicChildrenAction extends MainAction
{

    public $model = null;
    public $fields = [];

    const FIELDS_PROCESSOR = 'fields_processor';


    /**
     * Transforms array of settings into the @see DynamicChildrenProcessor objects
     * @throws InvalidConfigException
     */
    public function init() {
        parent::init(); // TODO: Change the autogenerated stub
        foreach($this->fields as $key => &$fields) {
            if(!isset($fields[self::FIELDS_PROCESSOR])) {
                throw new InvalidConfigException('Fields processor should be specified');
            }
            $fields_processor_class = $fields[self::FIELDS_PROCESSOR];
            unset($fields[self::FIELDS_PROCESSOR]);
            $fields = new $fields_processor_class($fields);
        }
    }


    /**
     * For each @see DynamicChildrenProcessor performs data loading
     */
    public function loadChildModelsFromRequest() {
        foreach($this->fields as &$fields) {
            $fields->loadChildModelsFromRequest();
            $fields->afterLoadChildModels();
        }
    }


    /**
     * Saves the "parent" model and it's children, described in the @see DynamicChildrenProcessor objects.
     * @return \yii\web\Response
     */
    public function save() {
        if($this->model->load(Yii::$app->request->post())) {
            if($this->model->save()) {
                foreach($this->fields as &$field) {
                    $field->saveChildModels($this->model);
                }
                return $this->controller->redirect(['update', 'id' => $this->model->id]);
            }
        }
    }


    /**
     * Searches children using info from @see DynamicChildrenProcessor object
     */
    public function findChildModels() {
        if(!$this->model->isNewRecord) {
            foreach($this->fields as &$field) {
                $field->findChildModels($this->model);
            }
        }
    }


    /**
     * Gets the "parent" model
     * @param $id
     * @return mixed
     */
    abstract public function getModel($id);


    /**
     * Gets the "parent" model and fills it with data.
     * @param null $id
     */
    public function run($id = null) {
        $this->model = $this->getModel($id);
        $this->model->load(Yii::$app->request->post());
    }


    public function getDefaultView() {
        return $this->controller->id . '-form';
    }


    /**
     * Returns the "parent" model class
     * @return array|mixed|null
     */
    public function getModelClass() {
        $model_class = parent::getModelClass();
        $model_class = $model_class ?? Yii::$app->request->get('model_class');
        return $model_class;
    }


}