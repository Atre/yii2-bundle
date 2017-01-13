<?php
namespace dezmont765\yii2bundle\widgets\alert;

use dezmont765\yii2bundle\components\Alert;
use dezmont765\yii2bundle\components\SafeArray;
use Yii;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 12.01.2017
 * Time: 20:15
 */
class AlertWidget extends \yii\base\Widget
{
    public $viewType = self::NORMAL_VIEW;
    public $general_message = null;
    public $general_color = null;
    public $success_alerts = null;
    public $warning_alerts = null;
    public $error_alerts = null;

    const NORMAL_VIEW = 'alert-view';
    const DETAILED_VIEW = 'alert-detailed-view';
    const GENERAL_MESSAGE = 'general_message';
    const GENERAL_COLOR = 'general_color';
    const SUCCESS_ALERTS = 'success_store';
    const WARNING_ALERTS = 'warning_store';
    const ERROR_ALERTS = 'error_store';

    public $alerts = [];


    public function init() {
        $this->alerts = SafeArray::init($this->alerts);
    }


    public static function messages() {
        return [
            Alert::ERROR => Yii::t('messages', 'Your request failed with errors:'),
            Alert::WARNING => Yii::t('messages', 'Your request ends with warnings:'),
            Alert::MESSAGE => Yii::t('messages', 'Your request ends successfully'),
            Alert::NONE => Yii::t('messages', 'Can not determine alert type'),
        ];
    }


    /**
     * returns color by general status
     * */
    public function getColor() {
        return self::$colors[Alert::getGeneralStatus($this->alerts)];
    }


    /**
     * @return mixed
     * returns message by general status
     */
    public function getGeneralMessage() {
        $title_message = self::messages()[Alert::getGeneralStatus($this->alerts)];
        return $title_message;
    }


    public static $colors = [
        Alert::MESSAGE => 'success',
        Alert::WARNING => 'warning',
        Alert::ERROR => 'danger',
        Alert::NONE => 'info'
    ];


    public function run() {
        return $this->render($this->viewType, [
            'general_message' => $this->getGeneralMessage(),
            'general_color' => $this->getColor(),
            'success_alerts' => $this->alerts[Alert::$stores[Alert::MESSAGE]],
            'warning_alerts' =>$this->alerts[Alert::$stores[Alert::WARNING]],
            'error_alerts' => $this->alerts[Alert::$stores[Alert::ERROR]],
        ]);
    }

}