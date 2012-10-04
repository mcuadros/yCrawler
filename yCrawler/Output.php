<?php
namespace yCrawler;

class Output {
    const INFO = 20;
    const DEBUG = 10;

    const NOTICE = 31;
    const WARNING = 30;
    const ERROR = 40;
    const EXCEPTION = 50; 
    const CUSTOM = 60; 

    static $constants;
    static $drivers = Array();

    public static function levelToString($level) {
        if ( !is_array(self::$constants) ) {
            $reflection = new \ReflectionClass(get_called_class());
            self::$constants = array_flip($reflection->getConstants());
        }

        if ( array_key_exists($level, self::$constants) ) {
            return self::$constants[$level];
        }

        return false;
    }

    public static function loadDriver($class) {
        $rClass = new \ReflectionClass($class);
        if ( !$rClass->isSubclassOf('yCrawler\Output_Base') ) {
            throw new Exception('The output driver class must extend Output_Base');
        }
        return self::$drivers[$class] = new $class;
    }

    public static function &getInstance($class) {
        if ( !array_key_exists($class, self::$drivers) ) {
            $return = false;
            return $return;
        }

        return self::$drivers[$class];
    }

    public static function __callStatic($method, $args) {
        $result = null;
        foreach(self::$drivers as &$driver) {
            if ( method_exists($driver, $method) ) $result = call_user_func_array(Array($driver, $method), $args);
        }

        return $result;
    }
}