<?php
namespace yCrawler;

class Config {
    private $config = Array();
	private $default = Array(
        'max_threads' => Array('int', 100),
        'max_threads_by_parser' => Array('int', 10),
        'max_execution_time' => Array('int', 20),

        'cache_path' => Array('path', '/tmp/yCrawler/'),
        'cache_folder_depth' => Array('int', 2),

        'utf8_dom_hack' =>  Array('boolean', true),

        'utf8' => Array('boolean', true),
        'max_retries' => Array('int', 3),
        'ssl_certificate' => Array('file', 'libs/cacert.pem'),
        'user_agent' => Array('string', 'yCrawler/Alpha'),
        'cookie' => Array('file', '/tmp/cookie'),
        'request_cache' => Array('int', 3600),
        'connection_timeout' => Array('int', 10),
        'interface' => Array('string', false),
        'headers' => Array('boolean', true),
    );

	public function loadConfig(array $config) {
        foreach($this->default as $setting => $config) {
            if ( isset($config[$setting]) ) $this->set($setting, $ini[$setting]);
            else $this->set($setting, $config[1]);
        }

        return $this->getConfig();
	}

    public function getConfig() {
        return $this->config;
    }

    public function get($setting) {
        if ( isset($this->config[$setting]) ) return $this->config[$setting];
        if ( isset($this->default[$setting]) ) return $this->default[$setting][1];
        return false;
    }

    public function set($setting, $value) {
        if ( !isset($this->default[$setting]) ) {
            throw new \InvalidArgumentException(sprintf('Unknown setting "%s"', $setting));
        }

        if ( $this->isValid($setting, $value) ) $this->config[$setting] = $value;
    }

    public function isValid($setting, &$value) {
        $valid = false;
        $type = $this->default[$setting][0];
        switch ($type) {
            case 'int':
                if ( is_integer($value) ) $valid = true; break;
            case 'boolean':
                if ( is_bool($value) ) $valid = true; break;
            case 'string':
                if ( is_string($value) ) $valid = true; break;
            case 'path':
                if ( is_dir($value) || mkdir($value) ) $valid = true;
                if ( $value[strlen($value)-1] == '/' ) $value[strlen($value)-1] = ' ';
                break; 
            case 'file':
                if ( is_file($value) || touch($value) ) $valid = true; break;
        }

        if ( !$valid ) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid %s value "%s" in %s setting', 
                $type, (string)$value, $setting
            ));
        }

        return true;
    }
}