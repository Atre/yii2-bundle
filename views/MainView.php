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


    public function endPage($ajaxMode = false) {
        $content = ob_get_clean();
        if($ajaxMode) {
            $cached_js_files = \Yii::$app->cache->get('jsFiles');
            $cached_css_files = \Yii::$app->cache->get('cssFiles');
            if(is_array($this->jsFiles)) {
                foreach($this->jsFiles as $position => &$jsFile) {
                    $jsFile = array_filter($jsFile, function ($key) use ($cached_js_files, $position) {
                        if(isset($cached_js_files[$position][$key])) {
                            return false;
                        }
                        else return true;
                    }, ARRAY_FILTER_USE_KEY);
                }
                if(!empty($this->jsFiles)) {
                    $cached_js_files = ArrayHelper::merge($cached_js_files, $this->jsFiles);
                    \Yii::$app->cache->set('jsFiles', $cached_js_files);
                }
            }
            if(is_array($this->cssFiles)) {
                $this->cssFiles = array_filter($this->cssFiles, function ($key) use ($cached_css_files) {
                    if(isset($cached_css_files[$key])) {
                        return false;
                    }
                    else return true;
                }, ARRAY_FILTER_USE_KEY);
                if(!empty($this->cssFiles)) {
                    $cached_css_files = ArrayHelper::merge($cached_css_files, $this->cssFiles);
                    \Yii::$app->cache->set('cssFiles', $cached_css_files);
                }
            }
        }
        else {
            \Yii::$app->cache->set('jsFiles', $this->jsFiles);
            \Yii::$app->cache->set('cssFiles', $this->cssFiles);
        }
        echo strtr($content, [
            self::PH_HEAD => $this->renderHeadHtml(),
            self::PH_BODY_BEGIN => $this->renderBodyBeginHtml(),
            self::PH_BODY_END => $this->renderBodyEndHtml($ajaxMode),
        ]);

        $this->clear();
    }

}