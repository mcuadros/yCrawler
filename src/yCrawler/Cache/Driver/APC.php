<?php
namespace yCrawler\Cache\Driver;
use yCrawler\Cache\Driver;

class APC implements Driver  {
    public function set($key, $data, $ttl = 0) {
        $content = array(
            'time' => time(),
            'ttl' => $ttl,          
            'data' => $data
        );
        
        return apc_store((string)$key, $content, $ttl);
    }

    public function get($key) {
        if ( !$result = apc_fetch($key) ) {
            return false;
        }

        if ( !is_array($result) ) return false;
        return $result['data'];
    }

    public function delete($key) {
        return apc_delete($key);
    }

    public function info($key) {
        if ( !$result = apc_fetch($key) ) {
            return false;
        }

        return $result;
    }

    public function clear() {
        return apc_clear_cache('user');
    }
}