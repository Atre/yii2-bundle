<?php
/**
 * Created by PhpStorm.
 * User: DezMonT
 * Date: 28.02.2015
 * Time: 23:07
 */
namespace dezmont765\yii2bundle\views;

use yii\web\View;

class MainView extends View
{
    const LAYOUT_STORAGE = 'layoutData';


    public function getLayoutData($tab_group_name) {
        return (isset($this->params[self::LAYOUT_STORAGE][$tab_group_name])
            ? $this->params[self::LAYOUT_STORAGE][$tab_group_name] : []);
    }


    /**
     * @param $data
     */
    public function setLayoutData($data) {
        $this->params[self::LAYOUT_STORAGE] = $data;
    }


    public function endBody() {
        parent::endBody();
        if(is_array($this->blocks)) {
            foreach($this->blocks as $block) {
                echo $block;
            }
        }
    }

}