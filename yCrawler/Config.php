<?php
namespace yCrawler;

class Config {
	static $default = Array(
        'max_threads' => Array('int', 100),
        'max_threads_by_parser' => Array('int', 10),
        'max_execution_time' => Array('int', 20),

        'cache_path' => Array('path', '/tmp/'),
        'cache_folder_depth' => Array('int', 2),

        'utf8_dom_hack' =>  Array('boolean', true),

        'utf8' => Array('boolean', true),
        'max_retries' => Array('int', 3),
        'ssl_certificate' => Array('file', 'libs/cacert.pem'),
        'user_agent' => Array('string', 'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25'),
        'cookie' => Array('file', '/tmp/cookie'),
        'request_cache' => Array('int', 3600),
        'connection_timeout' => Array('int', 10),
        'interface' => Array('string', false),
    );

    static $config = Array();

	public static function loadConfig($file) {
		if ( !file_exists($file) ) throw new Exception('Unable to load config file: ' . $file);

        $ini = parse_ini_file($file);
        Output::log('Loaded .ini file '. $file, Output::DEBUG);
        foreach(self::$default as $setting => $config) {
            if ( array_key_exists($setting, $ini) ) { 
                $value = $ini[$setting]; 
            } else { 
                $value = $config[1]; 
            }
            self::set($setting, $value);
        }

        return self::$config;
	}

    public static function getConfig() {
        return self::$config;
    }

    public static function get($setting) {
        if ( !array_key_exists($setting, self::$config) ) return null;
        return self::$config[$setting];
    }

    public static function set($setting, $value) {
        
        if ( !array_key_exists($setting, self::$default) ) throw new Exception('Failed config load. ' . $setting . ': Unknown setting');

        switch (self::$default[$setting][0]) {
            case 'int':
                if ( !is_numeric($value) ) {
                    throw new Exception('Failed config load. ' . $setting . ': Invalid numeric value (' . $value . ')');
                }
                $value = (int)$value;
                break;
            case 'boolean':
                if ( $value != '1' && $value != '0' && $value != 'true' && $value != 'false' && $value != 'yes' && $value != 'no' ) {
                    throw new Exception('Failed config load. ' . $setting . ': Invalid boolean value (' . $value . ')');
                }    
                $value = (boolean)$value;
                break;
            case 'string':
                if ( self::$default[$setting][1] === false && strlen($value) == 0 ) $value = false;
                break;
            case 'path':
                if ( !is_dir($value) ) {
                     if ( !mkdir($value) ) {
                        throw new Exception('Failed config load. ' . $setting . ': Wrong path (' . $value . ') not writable');
                    }
                }
                if ( $value[strlen($value)-1] == '/' ) $value[strlen($value)-1] = ' ';
                break; 
            case 'file':
                if ( !is_file($value) ) {
                    if ( !touch($value) ) {
                        throw new Exception('Failed config load. ' . $setting . ': Wrong file (' . $value . ') not writable');
                    }
                } else {
                    if ( !is_writable($value) ) {
                        throw new Exception('Failed config load. ' . $setting . ': Wrong file (' . $value . ') not writable');
                    }
                }
                break;
        }

        if ( self::$default[$setting][0] == 'boolean' ) Output::log('Loaded setting '.$setting .': ' . (int)$value, Output::DEBUG);
        else Output::log('Loaded setting '.$setting .': ' . $value, Output::DEBUG);
        return self::$config[$setting] = $value;
    }

}