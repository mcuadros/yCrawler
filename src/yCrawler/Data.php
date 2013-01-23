<?php
namespace yCrawler;

class Data extends Base {
    private $_data;

    public function __construct(Array $data = null) {
        if ( !$data ) $data = Array();
        $this->_data = $data;
    }

    public function set($name, $value) {
        return $this->_data[$name] = $value;
    } 

    public function remove($name) {
        unset($this->_data[$name]);
        return true;
    } 

    public function add($name, $step = 1) {
        if ( !array_key_exists($name, $this->_data) ) {
            return $this->_data[$name] = $step;
        }

        return $this->_data[$name] += $step;
    }

    public function take($name, $step = 1) {
        if ( !array_key_exists($name, $this->_data) ) {
            return $this->_data[$name] = $step * -1;
        } 

        return $this->_data[$name] -= $step;
    }

    public function get($name) {
        if ( !array_key_exists($name, $this->_data) ) return null;
        return $this->_data[$name];
    }

    public function sum(Data $data) {
        foreach($data->getAll() as $name => $value) {
            if ( is_bool($value) && $value === true ) {
                $this->add($name);
            } else if ( is_numeric($value) ) {
                $this->add($name, $value);
            } else if ( is_string($value) ) {
                $this->add($value);
            }
        }

        return true;
    }

    public function getAll() {
        return $this->_data;
    }

    public function __toString() {
        return (string)json_encode($this->_data);
    }

}
