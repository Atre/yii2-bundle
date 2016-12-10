<?php
namespace dezmont765\yii2bundle\components;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 09.03.2016
 * Time: 12:36
 */

class SafeArrayIterator implements \Iterator {

    /**
     * @var array list of keys in the map
     */
    private $_keys;
    /**
     * @var mixed current key
     */
    private $_key;


    private $_array = [];


    /**
     * Constructor.
     * @param $array
     */
    public function __construct($array)
    {
        $this->_array = $array;
        $this->_keys = array_keys($array);
    }

    /**
     * Rewinds internal array pointer.
     * This method is required by the interface [[\Iterator]].
     */
    public function rewind()
    {
        $this->_key = reset($this->_keys);
    }

    /**
     * Returns the key of the current array element.
     * This method is required by the interface [[\Iterator]].
     * @return mixed the key of the current array element
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * Returns the current array element.
     * This method is required by the interface [[\Iterator]].
     * @return mixed the current array element
     */
    public function current()
    {
        return isset($this->_array[$this->_key]) ? $this->_array[$this->_key] : null;
    }

    /**
     * Moves the internal pointer to the next array element.
     * This method is required by the interface [[\Iterator]].
     */
    public function next()
    {
        do {
            $this->_key = next($this->_keys);
        } while (!isset($this->_array[$this->_key]) && $this->_key !== false);
    }

    /**
     * Returns whether there is an element at current position.
     * This method is required by the interface [[\Iterator]].
     * @return boolean
     */
    public function valid()
    {
        return $this->_key !== false;
    }
}