<?php

namespace yCrawler\Cache;

/**
 * Interface que define cómo deben ser los drivers que gestionan la Cache
 */
interface Driver
{
    public function set($key, $data, $ttl = 0);
    public function get($key);
    public function delete($key);
    public function info($key);
    public function clear();
}
