<?php
namespace dezmont765\yii2bundle\behaviors;

use dezmont765\yii2bundle\events\FileSaveEvent;
use dezmont765\yii2bundle\models\MainActiveRecord;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * Created by PhpStorm.
 * User: DezMonT
 * Date: 19.04.2015
 * Time: 14:25
 * Class FileSaveBehavior
 * @property MainActiveRecord $owner
 * @property MainActiveRecord $lama
 */
class FileSaveRelationBehavior extends FileSaveBehavior
{
    const STORE_MODEL_CLASS = 'store_model_class';
    const STORE_RELATION_ATTRIBUTE = 'store_relation_attribute';
    const STORE_TYPE_ATTRIBUTE = 'store_type_attribute';
    const STORE_MODEL_ATTRIBUTE = 'store_model_attribute';
    const STORE_MODEL_TYPE = 'store_model_type';
    const IS_NEED_TO_KEEP_OLD_FILES = 'is_need_to_keep_old_files';
    const FILE_NAMES = 'file_names';
    const INSTANCES = 'instances';
    const STORE_PK_ATTRIBUTE = 'store_pk_attribute';


    public function requiredParams() {
        return ArrayHelper::merge(parent::requiredParams(), [
            self::STORE_MODEL_CLASS,
            self::STORE_RELATION_ATTRIBUTE,
            self::STORE_TYPE_ATTRIBUTE,
            self::STORE_MODEL_ATTRIBUTE,
            self::STORE_MODEL_TYPE,
        ]);
    }


    public function initialValues() {
        $initial_values = parent::initialValues();
        $initial_values = ArrayHelper::merge($initial_values, [
            self::FILE_NAMES => [],
            self::INSTANCES => [],
            self::STORE_PK_ATTRIBUTE => 'id',
        ]);
        return $initial_values;
    }


    public function events() {
        $events = ArrayHelper::merge(parent::events(), [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
        ]);
        return $events;
    }


    public function afterFind() {
        foreach($this->file_attributes as $attribute => $property) {
            $files = $this->findRelatedModel($attribute);
            $store_model_attribute = $this->file_attributes[$attribute][self::STORE_MODEL_ATTRIBUTE];
            if(count($files) > 0) {
                $this->file_attributes[$attribute][self::FILE_NAMES] = [];
                foreach($files as $file) {
                    $this->file_attributes[$attribute][self::FILE_NAMES][$file->id] = $file->$store_model_attribute;
                }
            }
        }
    }


    public function afterValidationProcess($attribute) {
        $this->file_attributes[$attribute][self::INSTANCES] = UploadedFile::getInstances($this->owner, $attribute);
        if(!$this->file_attributes[$attribute][self::IS_NEED_TO_KEEP_OLD_FILES]) {
            $instances = $this->file_attributes[$attribute][self::INSTANCES];
            if(count($instances) > 0) {
                if(!$this->owner->isNewRecord) {
                    /** @var string|MainActiveRecord $store_model_class */
                    $store_model_attribute = $this->file_attributes[$attribute][self::STORE_MODEL_ATTRIBUTE];
                    $files = $this->findRelatedModel($attribute);
                    foreach($files as $file) {
                        if($file->delete()) {
                            if(!empty($store_model_attribute)) {
                                $this->_unlink($this->getFileSaveDir($attribute) . $file->$store_model_attribute);
                                $this->deleteSimilarFiles($this->getFileSaveDir($attribute) .
                                                          $file->$store_model_attribute);
                            }
                        }
                    }
                }
            }
        }
    }


    public function postSavingProcess($attribute) {
        $instances = $this->file_attributes[$attribute][self::INSTANCES];
        if(is_array($instances) && count($instances)) {
            self::prepareFolderTunnel($this->getFileSaveDir($attribute),
                                      $this->getFileBaseSaveDir($attribute),
                                      $this->getBackendViewDir($attribute),
                                      $this->getFrontendViewDir($attribute)
            );
            $store_model_class = $this->file_attributes[$attribute][self::STORE_MODEL_CLASS];
            $store_type_attribute = $this->file_attributes[$attribute][self::STORE_TYPE_ATTRIBUTE];
            $store_model_type = $this->file_attributes[$attribute][self::STORE_MODEL_TYPE];
            $store_model_attribute = $this->file_attributes[$attribute][self::STORE_MODEL_ATTRIBUTE];
            $store_relation_attribute = $this->file_attributes[$attribute][self::STORE_RELATION_ATTRIBUTE];
            $is_old_files_are_kept = $this->file_attributes[$attribute][self::IS_NEED_TO_KEEP_OLD_FILES];
            $pk_attribute = $this->file_attributes[$attribute][self::PK_ATTRIBUTE];
            $store_pk_attribute = $this->file_attributes[$attribute][self::STORE_PK_ATTRIBUTE];
            if($is_old_files_are_kept) {
                if(!is_array($this->file_attributes[$attribute][self::FILE_NAMES])) {
                    $this->file_attributes[$attribute][self::FILE_NAMES] = [];
                }
            }
            else $this->file_attributes[$attribute][self::FILE_NAMES] = [];
            foreach($instances as $instance) {
                if($instance instanceof UploadedFile) {
                    /** @var MainActiveRecord $store_model */
                    $store_model = new $store_model_class;
                    $file_name = $this->getFileName();
                    $file_extension = $instance->extension;
                    $store_model->$store_model_attribute = $file_name . '.' . $file_extension;
                    $store_model->$store_type_attribute = $store_model_type;
                    $store_model->$store_relation_attribute = $this->owner->$pk_attribute;
                    if($store_model->save()) {
                        $this->file_attributes[$attribute][self::FILE_NAMES][$store_model->$store_pk_attribute] =
                            $store_model->$store_model_attribute;
                        $file_full_path = $this->getFileSaveDir($attribute) . $store_model->$store_model_attribute;
                        if($instance->saveAs($file_full_path)) {
                            $this->owner->trigger(static::EVENT_FILE_SAVE,
                                                  new FileSaveEvent([FileSaveEvent::FILE_ATTRIBUTE => $store_model_attribute,
                                                                     FileSaveEvent::FILE_PATH => $file_full_path
                                                                    ]));
                        }
                    }
                }
            }
        }
    }


    public function findRelatedModel($attribute) {
        $files = $this->getRelatedModelsQuery($attribute)->all();
        return $files;
    }


    /**
     * @param $attribute
     * @return ActiveQuery
     */
    public function getRelatedModelsQuery($attribute) {
        /** @var MainActiveRecord $store_model_class */
        $store_model_class = $this->file_attributes[$attribute][self::STORE_MODEL_CLASS];
        $store_relation_attribute = $this->file_attributes[$attribute][self::STORE_RELATION_ATTRIBUTE];
        $store_type_attribute = $this->file_attributes[$attribute][self::STORE_TYPE_ATTRIBUTE];
        $store_model_type = $this->file_attributes[$attribute][self::STORE_MODEL_TYPE];
        $pk_attribute = $this->file_attributes[$attribute][self::PK_ATTRIBUTE];
        $query = $store_model_class::find()
                                   ->where([$store_relation_attribute => $this->owner->$pk_attribute])
                                   ->andWhere([$store_type_attribute => $store_model_type]);
        return $query;
    }


    /** @method getFileViewPath
     * @param $attribute
     * @param bool $scheme
     * @param bool $append_timestamp
     * @return string
     */
    public function getFile($attribute, $scheme = false, $append_timestamp = false) {
        $result = [];
        $file_names = $this->file_attributes[$attribute][self::FILE_NAMES];
        if(is_array($file_names) && count($file_names)) {
            foreach($file_names as $file) {
                $result[] = $this->getFileByName($file, $attribute, $scheme, $append_timestamp);
            }
        }
        else {
            $files = $this->findRelatedModel($attribute);
            $store_model_attribute = $this->file_attributes[$attribute][self::STORE_MODEL_ATTRIBUTE];
            foreach($files as $file) {
                $result[] = $this->getFileByName($file->$store_model_attribute, $attribute, $scheme,
                                                 $append_timestamp);
            }
        }
        return $result;
    }


    public function getFileByRelationPk($file_attribute, $pk, $scheme = false, $append_timestamp = false) {
        $store_model_attribute = $this->file_attributes[$file_attribute][self::STORE_MODEL_ATTRIBUTE];
        $store_pk_attribute = $this->file_attributes[$file_attribute][self::STORE_PK_ATTRIBUTE];
        $model = $this->getRelatedModelsQuery($file_attribute)->andWhere([$store_pk_attribute => $pk])->one();
        $file = $this->getFileByName($model->$store_model_attribute, $file_attribute, $scheme, $append_timestamp);
        return $file;
    }


    public function getFilePhysicalPathByRelationPk($file_attribute, $pk) {
        $store_model_attribute = $this->file_attributes[$file_attribute][self::STORE_MODEL_ATTRIBUTE];
        $store_pk_attribute = $this->file_attributes[$file_attribute][self::STORE_PK_ATTRIBUTE];
        $model = $this->getRelatedModelsQuery($file_attribute)->andWhere([$store_pk_attribute => $pk])->one();
        $file = $this->getFileSaveDir($file_attribute) . $model->$store_model_attribute;
        return $file;
    }


    public function deleteFileByRelationPk($file_attribute, $id) {
        $store_model_attribute = $this->file_attributes[$file_attribute][self::STORE_MODEL_ATTRIBUTE];
        $store_pk_attribute = $this->file_attributes[$file_attribute][self::STORE_PK_ATTRIBUTE];
        $model = $this->getRelatedModelsQuery($file_attribute)->andWhere([$store_pk_attribute => $id])->one();
        $file = $this->getFileSaveDir($file_attribute) . $model->$store_model_attribute;
        if($model->delete()) {
            $this->_unlink($file);
            return true;
        }
        else return false;
    }


}