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

use dezmont765\yii2bundle\widgets\alert\AlertWidget;
use Exception;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use yii;
use yii\base\ErrorException;

class Alert extends yii\base\Component
{


    public function init() {
        Yii::$app->on('afterRequest', function () {
            $this->flashAlerts();
        });
    }


    public static function &getAlertsRef(&$alerts = null) {
        if($alerts === null) {
            return self::$alerts;
        }
        else return $alerts;
    }


    public function flashAlerts(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        if(count($alerts)) {
            foreach(self::$stores as $key => $store) {
                $alert = self::getAlertStoreByStatus($key, $alerts);
                if(!empty($alert)) {
                    Yii::$app->session->setFlash($store, $alert);
                }
            }
        }
    }


    public static $alerts = [];

    /** Types of alerts */
    const MESSAGE = 2;
    const WARNING = 1;
    const ERROR = 0;
    const NONE = -1;


    public static function getErrors(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        return self::getAlertStoreByStatus(self::ERROR, $alerts);
    }


    public static function getWarnings(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        return self::getAlertStoreByStatus(self::WARNING, $alerts);
    }


    public static function getMessages(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        return self::getAlertStoreByStatus(self::MESSAGE, $alerts);
    }


    public static $stores = [
        self::ERROR => 'FrError',
        self::WARNING => 'FrWarning',
        self::MESSAGE => 'FrMessage',
    ];


    public static $general_statuses = [
        '100' => self::MESSAGE,
        '010' => self::WARNING,
        '001' => self::ERROR,
        '000' => self::NONE,
    ];


    /**
     * @param $status
     * @param $msg
     * @param null $details
     * Adds an alert to proper store by status
     * @param null $alerts
     */
    public static function addAlert($status, $msg, $details = null, &$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        $assertion = true;
        if(Yii::$app->request instanceof yii\web\Request) {
            $assertion = !Yii::$app->request->isAjax;
        }
        if(Yii::$app->request instanceof yii\console\Request) {
            $assertion = true;
        }
        if($assertion) {
            $buffer = self::getAlertStoreByStatus($status, $alerts);
            $buffer[] = ['msg' => $msg,
                         'details' => $details];
            self::setAlert($status, $buffer, $alerts);
        }
    }


    public static function popAlert($status, &$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        $buffer = self::getAlertStoreByStatus($status, $alerts);
        $last_message = array_slice($buffer, 0, -1);
        return $last_message['msg'];
    }


    public static function popSuccess(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        return self::popAlert(self::MESSAGE, $alerts);
    }


    public static function popError(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        return self::popAlert(self::ERROR, $alerts);
    }


    public static function popWarning(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        return self::popAlert(self::WARNING, $alerts);
    }


    /**
     * @param $msg
     * @param null $details
     * Wraps the addAlert with predefined status
     * @param null $alerts
     */
    public static function addSuccess($msg, $details = null, &$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        if(!empty($msg)) {
            Yii::info($msg . ' : ' . json_encode($details));
            self::addAlert(self::MESSAGE, $msg, $details, $alerts);
        }
    }


    /**
     * @param $msg
     * @param null $details
     * Wraps the addAlert with predefined status
     * @param null $alerts
     */
    public static function addWarning($msg, $details = null, &$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        if(!empty($msg)) {
            Yii::warning($msg . ' : ' . json_encode($details));
            self::addAlert(self::WARNING, $msg, $details, $alerts);
        }
    }


    /**
     * @param $msg
     * @param null $details
     * Wraps the addAlert with predefined status
     * @param null $alerts
     */
    public static function addError($msg, $details = null, &$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        if(!empty($msg)) {
            Yii::error($msg . ' : ' . json_encode($details));
            self::addAlert(self::ERROR, $msg, $details, $alerts);
        }
    }


    /**
     * @param $status
     * @param $buffer
     * load buffer array to proper store.
     * @param null $alerts
     */
    public static function setAlert($status, $buffer, &$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        $alerts[self::$stores[$status]] = $buffer;
    }


    /**
     * Prints all collected alerts with proper colors, and then deletes them
     * @return string
     */
    public static function printAlert() {
        $result = [];
        foreach(self::$stores as $key => $store) {
            $alert = self::getAlertStoreByStatus($key) ? self::getAlertStoreByStatus($key) :
                Yii::$app->session->getFlash($store);
            if(!empty($alert)) {
                $result[$store] = $alert;
            }
        }
        if(count($result)) {
            self::dropAlerts();
            return AlertWidget::widget(['alerts' => $result]);
        }
        else return '';
    }


    public static function varDumpAlert(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        if(self::issetAlerts($alerts)) {
            var_dump(self::getAlertStoreByStatus(self::MESSAGE));
            var_dump(self::getAlertStoreByStatus(self::WARNING));
            var_dump(self::getAlertStoreByStatus(self::ERROR));
            self::dropAlerts($alerts);
        }
    }


    /**
     * @param null $alerts
     * @return bool Checks , whether alerts are exist
     * Checks , whether alerts are exist
     */
    public static function issetAlerts(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        return self::issetAlertStore(self::MESSAGE, $alerts) || self::issetAlertStore(self::WARNING, $alerts) ||
               self::issetAlertStore(self::ERROR, $alerts);
    }


    /**
     * @param null $alerts
     * @return bool * Checks , whether errors are exist
     * Checks , whether errors are exist
     */
    public static function issetErrors(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        return self::issetAlertStore(self::ERROR, $alerts);
    }


    /**
     * @param null $alerts
     * @return bool * Checks , whether warnings are exist
     * Checks , whether warnings are exist
     */
    public static function issetWarnings(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        return self::issetAlertStore(self::WARNING, $alerts);
    }


    /**
     * @param $status
     * @param null $alerts
     * @return bool Checks, whether specified store exists
     * Checks, whether specified store exists
     */
    public static function issetAlertStore($status, &$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        return isset($alerts[self::$stores[$status]]);
    }


    /**
     * @param $status
     * @param null $alerts
     * @return array returns the alert store by specified status
     * returns the alert store by specified status
     */
    public static function getAlertStoreByStatus($status, &$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        if(self::issetAlertStore($status, $alerts)) {
            return $alerts[self::$stores[$status]];
        }
        else {
            return [];
        }
    }


    /**
     * @param $status
     * deletes all alerts in specified store
     * @param null $alerts
     */
    public static function dropAlert($status, &$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        if(self::issetAlertStore($status, $alerts)) {
            unset($alerts[self::$stores[$status]]);
        }
    }


    /**
     * deletes all alerts
     * @param $alerts
     */
    public static function dropAlerts(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        self::dropAlert(self::MESSAGE, $alerts);
        self::dropAlert(self::WARNING, $alerts);
        self::dropAlert(self::ERROR, $alerts);
    }


    /**
     * @param null $alerts
     * @return mixed returns general status by mix of all statuses
     * returns general status by mix of all statuses
     */
    public static function getGeneralStatus(&$alerts = null) {
        $alerts = &self::getAlertsRef($alerts);
        $warning = count(self::getAlertStoreByStatus(self::WARNING, $alerts));
        $success = count(self::getAlertStoreByStatus(self::MESSAGE, $alerts));
        $error = count(self::getAlertStoreByStatus(self::ERROR, $alerts));
        $succ = (int)($success >= 1 && $warning == 0 && $error == 0);
        $warn = (int)(($success >= 1 && $error >= 1) || $warning >= 1);
        $err = (int)($success == 0 && $warning == 0 && $error >= 1);
        return self::$general_statuses[$succ . $warn . $err];
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
        return null;
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