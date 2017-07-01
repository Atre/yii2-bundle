<?php
namespace dezmont765\yii2bundle\widgets\alert;

use dezmont765\yii2bundle\components\MessageLogger;
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
            MessageLogger::ERROR => Yii::t('messages', 'Your request failed with errors:'),
            MessageLogger::WARNING => Yii::t('messages', 'Your request ends with warnings:'),
            MessageLogger::MESSAGE => Yii::t('messages', 'Your request ends successfully'),
            MessageLogger::NONE => Yii::t('messages', 'Can not determine alert type'),
        ];
    }


    /**
     * returns color by general status
     * */
    public function getColor() {
        return self::$colors[MessageLogger::getGeneralStatus($this->alerts)];
    }


    /**
     * @return mixed
     * returns message by general status
     */
    public function getGeneralMessage() {
        $title_message = self::messages()[MessageLogger::getGeneralStatus($this->alerts)];
        return $title_message;
    }


    public static $colors = [
        MessageLogger::MESSAGE => 'success',
        MessageLogger::WARNING => 'warning',
        MessageLogger::ERROR => 'danger',
        MessageLogger::NONE => 'info'
    ];


    public function run() {
        return $this->render($this->viewType, [
            'general_message' => $this->getGeneralMessage(),
            'general_color' => $this->getColor(),
            'success_alerts' => $this->alerts[MessageLogger::$stores[MessageLogger::MESSAGE]],
            'warning_alerts' => $this->alerts[MessageLogger::$stores[MessageLogger::WARNING]],
            'error_alerts' => $this->alerts[MessageLogger::$stores[MessageLogger::ERROR]],
        ]);
    }

}