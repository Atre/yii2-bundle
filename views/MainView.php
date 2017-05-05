<?php
/**
 * Created by PhpStorm.
 * User: DezMonT
 * Date: 28.02.2015
 * Time: 23:07
 */

namespace dezmont765\yii2bundle\views;

use yii\helpers\ArrayHelper;
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


    public function filterJsContainer(&$container, $temp_storage_name) {
        $temp_version = \Yii::$app->session->get($temp_storage_name);
        if(is_array($container)) {
            foreach($container as $position => &$jsFile) {
                $jsFile = array_filter($jsFile, function ($key) use ($temp_version, $position) {
                    if(isset($temp_version[$position][$key])) {
                        return false;
                    }
                    else return true;
                }, ARRAY_FILTER_USE_KEY);
            }
            if(!empty($container)) {
                foreach($container as $position => $scripts) {
                    if(!empty($scripts)) {
                        if(!isset($temp_version[$position]) || !is_array($temp_version[$position])) {
                            $temp_version[$position] = [];
                        }
                        $temp_version[$position] = array_merge($temp_version[$position], $scripts);
                    }
                }
                \Yii::$app->session->set($temp_storage_name, $temp_version);
            }
        }
    }


    public function endPage($ajaxMode = false) {
        $content = ob_get_clean();
        if($ajaxMode) {
            $this->filterJsContainer($this->jsFiles, 'jsFiles');
//            $this->filterJsContainer($this->js, 'js');
            $cached_css_files = \Yii::$app->session->get('cssFiles');
            if(is_array($this->cssFiles)) {
                $this->cssFiles = array_filter($this->cssFiles, function ($key) use ($cached_css_files) {
                    if(isset($cached_css_files[$key])) {
                        return false;
                    }
                    else return true;
                }, ARRAY_FILTER_USE_KEY);
                if(!empty($this->cssFiles)) {
                    $cached_css_files = ArrayHelper::merge($cached_css_files, $this->cssFiles);
                    \Yii::$app->session->set('cssFiles', $cached_css_files);
                }
            }
        }
        else {
            \Yii::$app->session->set('jsFiles', $this->jsFiles);
            \Yii::$app->session->set('cssFiles', $this->cssFiles);
            \Yii::$app->session->set('js', $this->js);
        }
        echo strtr($content, [
            self::PH_HEAD => $this->renderHeadHtml(),
            self::PH_BODY_BEGIN => $this->renderBodyBeginHtml(),
            self::PH_BODY_END => $this->renderBodyEndHtml($ajaxMode),
        ]);
        $this->clear();
    }
}