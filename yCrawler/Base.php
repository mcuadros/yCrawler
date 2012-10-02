<?php
namespace yCrawler;

/**
 * Clase abstracta que define los métodos necesarios para acceder a _data y _constants
 */
abstract class Base {
    
    /**
     * 
     * @var Data 
     */
    private $_data;
    private $_constants;


    public function &getData() { return $this->_data; }
    
    protected function data($function, $name, $value = null) {
        if ( !$this->_data ) $this->_data = new Data();
        $callback = Array($this->_data, $function); 

        if ( !is_callable($callback) ) { return false; }

        $params = Array($name);
        if ( $value !== null ) $params[] = $value;

        return call_user_func_array($callback, $params);
    }


    public function getConstant($value) {
        if ( !is_array($this->_constants) ) {
            $reflection = new \ReflectionClass(get_called_class());
            $this->_constants = array_flip($reflection->getConstants());
        }

        if ( array_key_exists($value, $this->_constants) ) {
            return strtolower($this->_constants[$value]);
        }

        return false;
    }

    public function __toString() {
        return (string)$this->_data;
    }


}