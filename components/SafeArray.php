<?php
namespace dezmont765\yii2bundle\components;
use Traversable;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 09.03.2016
 * Time: 12:33
 */
class SafeArray implements \IteratorAggregate, \ArrayAccess, \Countable
{
    private $_array = [];

    public static function init($array = []) {
        $safe_array = new SafeArray($array);
        return $safe_array;
    }

    public static function toSafe(array $arrays = []) {
        $safe_array = new SafeArray();
        foreach($arrays as $key => $value) {
            if(is_array($value)) {
                $safe_array[$key] = self::toSafe($value);
            }
            else $safe_array[$key] = $value;
        }
        return $safe_array;
    }

    public function __construct($array = []) {
        $this->_array = $array;
    }
    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator() {
        return new SafeArrayIterator($this->_array);
    }


    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset) {
        return isset($this->_array[$offset]) || array_key_exists($offset,$this->_array);
    }


    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset) {
        return self::offsetExists($offset) ? $this->_array[$offset] : null;
    }


    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value) {
        if($offset === null) {
            $this->_array[] = $value;
        }
        else $this->_array[$offset] = $value;
    }


    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset) {
        unset($this->_array[$offset]);
    }


    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count() {
        return count($this->_array);
    }

    public function __get($name) {
       return self::offsetGet($name);
    }

    public function __set($name, $value) {
       self::offsetSet($name,$value);
    }


}