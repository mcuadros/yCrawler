<?php

namespace yCrawler\Crawler\Request;

class Config
{
    public static $default = Array(
        'max_threads' => Array('int', 100),
        'max_threads_by_parser' => Array('int', 10),
        'max_execution_time' => Array('int', 20),

        'cache_path' => Array('path', '/tmp/yCrawler/'),
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
        'headers' => Array('boolean', true),
    );

    public static $config = Array();

    public static function loadConfig(array $config)
    {
        throw new \RuntimeException('Deprecated');
        foreach ($config as $setting => $value) {
            self::set($setting, $value);
        }

        return self::getConfig();
    }

    public static function getConfig()
    {
        throw new \RuntimeException('Deprecated');
        return self::$config;
    }

    public static function get($setting)
    {
        throw new \RuntimeException('Deprecated');
        if (isset(self::$config[$setting])) return self::$config[$setting][1];
        if (isset(self::$default[$setting])) return self::$default[$setting][1];
        return null;
    }

    public static function set($setting, $value)
    {
        throw new \RuntimeException('Deprecated');
        if (!isset(self::$default[$setting])) {
            throw new \InvalidArgumentException(sprintf('Unknown setting "%s"', $setting));
        }

        if (self::isValid($setting, $value)) self::$config[$setting] = $value;
    }

    public static function isValid($setting, &$value)
    {
        throw new \RuntimeException('Deprecated');
        $valid = false;
        $type = self::$default[$setting][0];
        switch ($type) {
            case 'int':
                if (is_integer($value)) $valid = true; break;
            case 'boolean':
                if (is_bool($value)) $valid = true; break;
            case 'string':
                if (is_string($value)) $valid = true; break;
            case 'path':
                if (is_dir($value) || mkdir($value)) $valid = true;
                if ($value[strlen($value)-1] == '/') $value[strlen($value)-1] = ' ';
                break;
            case 'file':
                if (is_file($value) || touch($value)) $valid = true; break;
        }

        if (!$valid) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid %s value "%s" in %s setting',
                $type, (string) $value, $setting
            ));
        }

        return true;
    }
}

