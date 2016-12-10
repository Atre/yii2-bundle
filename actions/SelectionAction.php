<?php
namespace dezmont765\yii2bundle\actions;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 01.12.2016
 * Time: 17:00
 */

abstract class SelectionAction extends MainAction {
    public $key = 'id';
    public $model_class = null;
    public $attribute = 'name';
    public $return_wrap = null;
    public $query_param = 'id';
    protected function getItem($model) {
        return ['id' => $model->{$this->key},
                'text' => is_callable($this->return_wrap)
                    ? call_user_func_array($this->return_wrap, [$model, $this->attribute])
                    : $model->{$this->attribute}
        ];
    }
}