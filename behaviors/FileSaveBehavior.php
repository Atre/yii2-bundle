<?php
namespace dezmont765\yii2bundle\behaviors;

use dezmont765\yii2bundle\components\Encryption;
use dezmont765\yii2bundle\events\FileSaveEvent;
use dezmont765\yii2bundle\models\MainActiveRecord;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
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
class FileSaveBehavior extends Behavior
{
    public $crop;

    const EVENT_FILE_SAVE = 'file_save';
    const EVENT_FILE_DELETE = 'file_delete';

    const INSTANCE = 'instance';
    /**
     * Full real path to a real file folder, usually stored in common
     */
    const FILE_BASE_SAVE_DIR = 'file_save_dir';
    /**
     * Public relative path to a file folder
     */
    const FILE_VIEW_BASE_URL = 'file_view_url';
    /**
     * Path to a backend folder, which is actually a symlink to a folder, stored in common
     */
    const BACKEND_VIEW_DIR = 'backend_view_dir';
    /**
     * Path to a frontend folder, which is actually a symlink to a folder, stored in common
     */
    const FRONTEND_VIEW_DIR = 'frontend_view_dir';
    /**
     * Callback, called on save
     */
    const ON_SAVE = 'on_save';
    /**
     * Callback, called on delete
     */
    const ON_DELETE = 'on_delete';
    /**
     * Whether to use encrypt folder name or not, 'false' by default
     */
    const IS_ENCRYPT = 'is_encrypt';
    /**
     * Which attribute is used to build a folder name, 'id' by default.
     */
    const FILE_FOLDER_ATTRIBUTE = 'file_folder_attribute';
    /**
     * Primary key property name
     */
    const PK_ATTRIBUTE = 'pk_attribute';

    public $file_attributes = [];


    public function requiredParams() {
        return [
            self::FILE_BASE_SAVE_DIR,
            self::FILE_VIEW_BASE_URL,
            self::BACKEND_VIEW_DIR,
            self::FRONTEND_VIEW_DIR,
            self::FILE_FOLDER_ATTRIBUTE,
        ];
    }


    public function initialValues() {
        return [
            self::INSTANCE => null,
            self::ON_SAVE => null,
            self::ON_DELETE => null,
            self::IS_ENCRYPT => false,
            self::PK_ATTRIBUTE => 'id',
            self::FILE_FOLDER_ATTRIBUTE => 'id'
        ];
    }


    public function init() {
        foreach($this->file_attributes as $key => &$params) {
            $params = ArrayHelper::merge($this->initialValues(), $params);
            $required_params = array_flip($this->requiredParams());
            foreach($required_params as $param_name => $required_param) {
                if(empty($params[$param_name])) {
                    throw new InvalidParamException($param_name . ' can not be empty');
                }
            }
//
        }
    }


    public function attach($owner) {
        parent::attach($owner);
        foreach($this->file_attributes as $key => $params) {
            if(!empty($params[self::ON_SAVE])) {
                $this->owner->on(static::EVENT_FILE_SAVE, $params[self::ON_SAVE]);
            }
            if(!empty($params[self::ON_DELETE])) {
                $this->owner->on(static::EVENT_FILE_SAVE, $params[self::ON_DELETE]);
            }
        }
    }


    public function events() {
        return [
            ActiveRecord::EVENT_AFTER_VALIDATE => 'afterValidate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
        ];
    }


    public function afterDeleteProcess($attribute) {
        FileHelper::removeDirectory($this->getFileSaveDir($attribute));
    }


    public function afterDelete() {
        foreach($this->file_attributes as $file_attribute => $property) {
            self::afterDeleteProcess($file_attribute);
        }
    }


    /**
     * Deletes all files and folders with name starting from $name
     * @param $file
     */
    public function deleteSimilarFiles($file) {
        $name = pathinfo($file, PATHINFO_FILENAME);
        $path = pathinfo($file, PATHINFO_DIRNAME);
        $files = glob($path . DIRECTORY_SEPARATOR . $name . '*');
        foreach($files as $file) {
            $this->_unlink($file);
            if(is_dir($file)) {
                FileHelper::removeDirectory($file);
            }
        }
    }


    /**
     * Safe file unlink
     * @param $file_path
     * @return bool
     */
    public function _unlink($file_path) {
        if(is_file($file_path)) {
            unlink($file_path);
            return true;
        }
        return false;
    }


    /**
     * Generates file folder name from an owner model
     * @param $attribute
     * @throws InvalidParamException
     * @return string
     */
    public function getFileFolderName($attribute) {
        $file_folder_attribute = $this->file_attributes[$attribute][self::FILE_FOLDER_ATTRIBUTE];
        if(empty($file_folder_attribute)) {
            throw new InvalidParamException('FILE_FOLDER_ATTRIBUTE can not be empty');
        }
        $file_folder_name = $this->owner->{$file_folder_attribute};
        if($this->file_attributes[$attribute][self::IS_ENCRYPT]) {
            return Encryption::encode($file_folder_name);
        }
        else return $file_folder_name;
    }


    public function afterValidationProcess($attribute) {
        $this->file_attributes[$attribute][self::INSTANCE] =
        $file_instance = UploadedFile::getInstance($this->owner, $attribute);
        if($file_instance instanceof UploadedFile) {
            if(!$this->owner->isNewRecord) {
                if(isset($this->owner->oldAttributes[$attribute])) {
                    $this->_unlink($this->getFileSaveDir($attribute) . $this->owner->oldAttributes[$attribute]);
                }
            }
            $this->owner->$attribute = $this->composeFileName($attribute);
        }
        else {
            if(isset($this->owner->oldAttributes[$attribute]) && $this->owner->oldAttributes) {
                $this->owner->$attribute = $this->owner->oldAttributes[$attribute];
            }
        }
    }


    public function afterValidate($event) {
        foreach($this->file_attributes as $file_attribute => $property) {
            $this->afterValidationProcess($file_attribute);
        }
    }


    public static function isDirEmpty($dir) {
        if(!is_readable($dir)) return null;
        $handle = opendir($dir);
        while(false !== ($entry = readdir($handle))) {
            if($entry != "." && $entry != "..") {
                return false;
            }
        }
        return true;
    }


    /**
     * Creates a tunnel between web accessible folder and real folder, using symlinks.
     * 1) Checks and creates folder if needed;
     * 2) Checks and creates frontend/backend symlinks if needed, for web access purposes.
     * @param $file_save_path
     * @param $file_save_dir
     * @param $backend_view_dir
     * @param $frontend_view_dir
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public static function prepareFolderTunnel($file_save_path, $file_save_dir, $backend_view_dir = null, $frontend_view_dir = null) {
        if(!is_dir($file_save_path)) {
            FileHelper::createDirectory($file_save_path);
        }
        if($backend_view_dir !== null) {
            if(!self::_is_link($backend_view_dir)) {
                if(is_dir($backend_view_dir)) {
                    FileHelper::removeDirectory($backend_view_dir);
                }
                symlink($file_save_dir, $backend_view_dir);
            }
        }
        if($frontend_view_dir !== null) {
            if(!self::_is_link($frontend_view_dir)) {
                if(is_dir($frontend_view_dir)) {
                    FileHelper::removeDirectory($frontend_view_dir);
                }
                symlink($file_save_dir, $frontend_view_dir);
            }
        }
    }


    public static function _is_link($target) {
        if(is_link($target)) {
            return true;
        }
        $real_path = realpath($target);
        if($real_path && $real_path !== $target) {
            return true;
        }
        return false;
    }


    public function postSavingProcess($attribute) {
        if($this->file_attributes[$attribute][self::INSTANCE] instanceof UploadedFile) {
            $this->prepareFolderTunnel($this->getFileSaveDir($attribute),
                                       $this->getFileBaseSaveDir($attribute),
                                       $this->getBackendViewDir($attribute),
                                       $this->getFrontendViewDir($attribute)
            );
            $file_path = $this->getFileSaveDir($attribute) . $this->owner->$attribute;
            if($this->getFileInstance($attribute)->saveAs($file_path)) {
                $this->owner->trigger(static::EVENT_FILE_SAVE,
                                      new FileSaveEvent([FileSaveEvent::FILE_ATTRIBUTE => $attribute,
                                                         FileSaveEvent::FILE_PATH => $file_path]));
            }
        }
    }


    public function afterSave($event) {
        foreach($this->file_attributes as $file_attribute => $property) {
            $this->postSavingProcess($file_attribute);
        }
    }


    /**
     * @param $file_attribute
     * @return UploadedFile | null
     */
    public function getFileInstance($file_attribute) {
        if(isset($this->file_attributes[$file_attribute])) {
            return $this->file_attributes[$file_attribute][self::INSTANCE];
        }
        else return null;
    }


    public function getFileAttributeParams($file_attribute) {
        if(isset($this->file_attributes[$file_attribute])) {
            return $this->file_attributes[$file_attribute];
        }
        else return [];
    }


    /**
     * Returns a path to a backend folder (symlink)
     * @param $file_attribute
     * @return bool|null|string
     */
    public function getBackendViewDir($file_attribute) {
        $path = null;
        if(isset($this->file_attributes[$file_attribute][self::BACKEND_VIEW_DIR])) {
            $path = Yii::getAlias($this->file_attributes[$file_attribute][self::BACKEND_VIEW_DIR]);
        }
        return $path;
    }


    /**
     * Returns a path to a frontend folder (symlink)
     * @param $file_attribute
     * @return bool|null|string
     */
    public function getFrontendViewDir($file_attribute) {
        $path = null;
        if(isset(self::getFileAttributeParams($file_attribute)[self::FRONTEND_VIEW_DIR])) {
            $path = Yii::getAlias(self::getFileAttributeParams($file_attribute)[self::FRONTEND_VIEW_DIR]);
        }
        return $path;
    }


    /**
     * Returns a base folder for all files from current group of files, set by $file_attribute
     * Eventual folder of a certain file depends on an owner model @see getFileSaveDir
     * @param $file_attribute
     * @return bool|string
     */
    public function getFileBaseSaveDir($file_attribute) {
        if(isset(self::getFileAttributeParams($file_attribute)[self::FILE_BASE_SAVE_DIR])) {
            return Yii::getAlias(self::getFileAttributeParams($file_attribute)[self::FILE_BASE_SAVE_DIR]);
        }
        else throw new InvalidParamException();
    }


    /**
     * Returns a file root folder, which depends on an owner model
     * @param $file_attribute
     * @return string
     */
    public function getFileSaveDir($file_attribute) {
        return self::getFileBaseSaveDir($file_attribute) . self::getFileFolderName($file_attribute) .
               DIRECTORY_SEPARATOR;
    }


    /**@method getFileViewUrl
     * Returns a file root folder
     * @param $file_attribute
     * @return bool|string
     */
    public function getFileViewBaseUrl($file_attribute) {
        if(isset(self::getFileAttributeParams($file_attribute)[self::FILE_VIEW_BASE_URL])) {
            return Yii::getAlias(self::getFileAttributeParams($file_attribute)[self::FILE_VIEW_BASE_URL]);
        }
        else throw new InvalidParamException();
    }


    /** @method getFileViewPath
     * @param $file_attribute
     * @return string
     */
    public function getFileViewUrl($file_attribute) {
        return self::getFileViewBaseUrl($file_attribute) . '/' . self::getFileFolderName($file_attribute) . '/';
    }


    /** @method getFileViewPath
     * @param $attribute
     * @param bool $scheme
     * @param bool $append_timestamp
     * @return string
     */
    public function getFile($attribute, $scheme = false, $append_timestamp = false) {
        $result = $this->getFileByName($this->owner->$attribute, $attribute, $scheme, $append_timestamp);
        return $result;
    }


    public function getFilePhysicalPath($attribute) {
        return $this->getFileSaveDir($attribute) . $this->owner->$attribute;
    }


    public function deleteFile($file_attribute) {
        if($this->_unlink($this->getFilePhysicalPath($file_attribute))) {
            $this->owner->$file_attribute = null;
            $this->owner->updateAttributes([$file_attribute]);
            return true;
        }
        else return false;
    }


    public function getFileByName($name, $attribute, $scheme = false, $append_timestamp = false) {
        $file_save_path = self::getFileSaveDir($attribute) . $name;
        $file_view_path = self::getFileViewUrl($attribute) . $name;
        if($append_timestamp && ($timestamp = @filemtime($file_save_path)) > 0) {
            $file = $file_view_path . '?timestamp=' . $timestamp;
        }
        else {
            $file = $file_view_path;
        }
        if($scheme) {
            return Url::to([$file], $scheme);
        }
        else return $file;
    }


    /** @method getFileName
     * @return string
     */
    public function getFileName() {
        return Yii::$app->security->generateRandomString(16);
    }


    public function composeFileName($attribute) {
        $instance = $this->file_attributes[$attribute][self::INSTANCE];
        if($instance instanceof UploadedFile) {
            $file_name = $this->getFileName() . '.' . $instance->extension;
            return $file_name;
        }
        else return null;
    }


    public function saveFiles() {
        $this->owner->id = Yii::$app->security->generateRandomString(8);
        foreach($this->file_attributes as $file_attribute => $property) {
            self::afterValidationProcess($file_attribute);
            self::postSavingProcess($file_attribute);
        }
    }


}