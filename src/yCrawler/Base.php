<?php

namespace yCrawler;

/**
 * Clase abstracta que define los métodos necesarios para acceder a _data y _constants
 */
abstract class Base
{
    private $crawler;
    private $data;
    private $constants;

    public function __construct(Crawler $crawler = null)
    {
        $this->crawler = $crawler;
    }

    public function &getData() { return $this->data; }

    public function getConstant($value)
    {
        if (!is_array($this->_constants)) {
            $reflection = new \ReflectionClass(get_called_class());
            $this->_constants = array_flip($reflection->getConstants());
        }

        if ( array_key_exists($value, $this->_constants) ) {
            return strtolower($this->_constants[$value]);
        }

        return false;
    }

    protected function writeCache($key, $data, $ttl = 0, $persist = false)
    {
        if ($persist) $cache = $this->crawler['cache.persistent'];
        else $cache = $this->crawler['cache.memory'];
        $cache->set($key, $data, $ttl);
    }

    protected function readCache($key, $persist = false)
    {
        if ($persist) $cache = $this->crawler['cache.persistent'];
        else $cache = $this->crawler['cache.memory'];
        $cache->get($key);
    }

    protected function data($function, $name, $value = null)
    {
        if (!$this->data) $this->data = new Data();

        $callback = Array($this->data, $function);
        if (!is_callable($callback)) { return false; }

        $params = Array($name);
        if ($value !== null) $params[] = $value;
        return call_user_func_array($callback, $params);
    }

    protected function config($setting)
    {
        if (!$this->crawler) return false;
        return $this->crawler['config']->get($setting);
    }

    public function __toString()
    {
        return (string) $this->_data;
    }
}
