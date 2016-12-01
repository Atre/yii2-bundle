<?php
/**
 * Created by JetBrains PhpStorm.
 * User: DezMonT
 * Date: 10.09.14
 * Time: 20:34
 * To change this template use File | Settings | File Templates.
 */
/**
 * Class Alert
 * Nice class to show flash messages to the user.
 */
namespace dezmont765\yii2bundle\components;

use Exception;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use yii;
use yii\base\ErrorException;
use yii\web\Request;

class Alert
{

    /** Types of alerts */
    const SUCCESS = 2;
    const WARNING = 1;
    const ERROR = 0;
    const NONE = -1;


    public static function getErrors() {
        return self::getAlertStore(self::ERROR);
    }


    public static $stores = [
        self::ERROR => 'FrError',
        self::WARNING => 'FrWarning',
        self::SUCCESS => 'FrSuccess',
    ];


    public static function messages() {
        return [
            self::ERROR => Yii::t('messages', 'Your request failed with errors:'),
            self::WARNING => Yii::t('messages', 'Your request ends with warnings:'),
            self::SUCCESS => Yii::t('messages', 'Your request ends successfully'),
            self::NONE => Yii::t('messages', 'Can not determine alert type'),
        ];
    }


    public static $general_statuses = [
        '100' => self::SUCCESS,
        '010' => self::WARNING,
        '001' => self::ERROR,
        '000' => self::NONE,
    ];

    public static $colors = [
        self::SUCCESS => 'success',
        self::WARNING => 'warning',
        self::ERROR => 'danger',
        self::NONE => 'info'
    ];


    /**
     * @param $status
     * @param $msg
     * @param null $details
     * Adds an alert to proper store by status
     */
    public static function addAlert($status, $msg, $details = null) {
        $assertion = true;
        if(Yii::$app->request instanceof yii\web\Request) {
            $assertion = !Yii::$app->request->isAjax;
        }
        if(Yii::$app->request instanceof yii\console\Request) {
            $assertion = true;
        }
        if($assertion) {
            $buffer = self::getAlertStore($status);
            $buffer[] = ['msg' => $msg,
                         'details' => $details];
            self::setAlert($status, $buffer);
        }
    }


    public static function popAlert($status) {
        $buffer = self::getAlertStore($status);
        $last_message = array_slice($buffer, 0, -1);
        return $last_message['msg'];
    }


    public static function popSuccess() {
        return self::popAlert(self::SUCCESS);
    }


    public static function popError() {
        return self::popAlert(self::ERROR);
    }


    public static function popWarning() {
        return self::popAlert(self::WARNING);
    }


    /**
     * @param $msg
     * @param null $details
     * Wraps the addAlert with predefined status
     */
    public static function addSuccess($msg, $details = null) {
        Yii::info($msg . ' : ' . json_encode($details));
        self::addAlert(self::SUCCESS, $msg, $details);
    }


    /**
     * @param $msg
     * @param null $details
     * Wraps the addAlert with predefined status
     */
    public static function addWarning($msg, $details = null) {
        Yii::warning($msg . ' : ' . json_encode($details));
        self::addAlert(self::WARNING, $msg, $details);
    }


    /**
     * @param $msg
     * @param null $details
     * Wraps the addAlert with predefined status
     */
    public static function addError($msg, $details = null) {
        if(!empty($msg)) {
            Yii::error($msg . ' : ' . json_encode($details));
            self::addAlert(self::ERROR, $msg, $details);
        }
    }


    /**
     * @param $status
     * @param $buffer
     * load buffer array to proper store.
     */
    public static function setAlert($status, $buffer) {
        Yii::$app->session[self::$stores[$status]] = $buffer;
    }


    /**
     * Prints all collected alerts with proper colors, and then deletes them
     * @param $viewInstance
     * @return string
     */
    public static function printAlert(&$viewInstance) {
        /**@var \common\components\MainView $viewInstance */
        $result = '';
        $view = ParamsGetter::is_detailed_alert() ? 'alertView' : 'alertSmallView';
        if(self::issetAlerts()) {
            $result = $viewInstance->render('/' . $view, ['general_message' => self::getGeneralMessage(),
                                                          'general_color' => self::getColor(),
                                                          'success_store' => self::getAlertStore(self::SUCCESS),
                                                          'warning_store' => self::getAlertStore(self::WARNING),
                                                          'error_store' => self::getAlertStore(self::ERROR),
            ]);
            self::dropAlerts();
        }
        return $result;
    }


    public static function varDumpAlert() {
        if(self::issetAlerts()) {
            var_dump(self::getAlertStore(self::SUCCESS));
            var_dump(self::getAlertStore(self::WARNING));
            var_dump(self::getAlertStore(self::ERROR));
            self::dropAlerts();
        }
    }


    /**
     * @return bool
     * Checks , whether alerts are exist
     */
    public static function issetAlerts() {
        return self::issetAlert(self::SUCCESS) || self::issetAlert(self::WARNING) || self::issetAlert(self::ERROR);
    }


    /**
     * @return bool
     * * Checks , whether errors are exist
     */
    public static function issetErrors() {
        return self::issetAlert(self::ERROR);
    }


    /**
     * @return bool
     * * Checks , whether warnings are exist
     */
    public static function issetWarnings() {
        self::issetAlert(self::WARNING);
    }


    /**
     * @param $status
     * @return bool
     * Checks, whether specified store exists
     */
    public static function issetAlert($status) {
        return isset(Yii::$app->session[self::$stores[$status]]);
    }


    /**
     * @param $status
     * @return array
     * returns the alert store by specified status
     */
    public static function getAlertStore($status) {
        if(self::issetAlert($status)) {
            return Yii::$app->session[self::$stores[$status]];
        }
        else {
            return [];
        }
    }


    /**
     * @param $status
     * deletes all alerts in specified store
     */
    public static function dropAlert($status) {
        if(self::issetAlert($status)) {
            unset(Yii::$app->session[self::$stores[$status]]);
        }
    }


    /**
     * deletes all alerts
     */
    public static function dropAlerts() {
        self::dropAlert(self::SUCCESS);
        self::dropAlert(self::WARNING);
        self::dropAlert(self::ERROR);
    }


    /**
     * @return mixed
     * returns general status by mix of all statuses
     */
    public static function getGeneralStatus() {
        $warning = count(self::getAlertStore(self::WARNING));
        $success = count(self::getAlertStore(self::SUCCESS));
        $error = count(self::getAlertStore(self::ERROR));
        $succ = (int)($success >= 1 && $warning == 0 && $error == 0);
        $warn = (int)(($success >= 1 && $error >= 1) || $warning >= 1);
        $err = (int)($success == 0 && $warning == 0 && $error >= 1);
        return self::$general_statuses[$succ . $warn . $err];
    }


    /**
     * returns color by general status
     * */
    public static function getColor() {
        return self::$colors[self::getGeneralStatus()];
    }


    /**
     * @return mixed
     * returns message by general status
     */
    public static function getGeneralMessage() {
        $title_message = self::messages()[self::getGeneralStatus()];
        return $title_message;
    }


    static function recursiveFind(array $array, $needle) {
        $iterator = new RecursiveArrayIterator($array);
        $recursive = new RecursiveIteratorIterator($iterator,
                                                   RecursiveIteratorIterator::SELF_FIRST);
        foreach($recursive as $key => $value) {
            if($key === $needle) {
                return $value;
            }
        }
    }


    public static function getErrorInfo($params = null) {
        $error = null;
        if(empty($params)) {
            $fatal_error = error_get_last();
            if(ErrorException::isFatalError($fatal_error)) {
                $error = $fatal_error;
            }
        }
        else {
            $e = isset($params[0]) ? $params[0] : null;
            if($e instanceof Exception) {
                $error = [];
                $error['code'] = $e->getCode();
                $error['message'] = $e->getMessage();
                $error['file'] = $e->getFile();
                $error['line'] = $e->getLine();
            }
            if(empty($error) && isset($params[0])) {
                $error['code'] = isset($params[0]) ? $params[0] : null;
                $error['message'] = isset($params[1]) ? $params[1] : null;
                $error['file'] = isset($params[2]) ? $params[2] : null;
                $error['line'] = isset($params[3]) ? $params[3] : null;
            }
        }
        return $error;
    }


}