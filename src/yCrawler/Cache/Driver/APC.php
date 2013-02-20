<?php
namespace yCrawler\Cache\Driver;
use yCrawler\Cache\Driver;

class APC extends Base implements Driver  {
    public function set($key, $data, $ttl = 0) {
        $content = array(
            'time' => time(),
            'ttl' => $ttl,          
            'data' => serialize($data)
        );
        
        return apc_store((string)$key, $content, $ttl);
    }

    public function get($key) {
        if ( !$result = apc_fetch($key) ) {
            return false;
        }

        if ( $result['ttl'] > 0 && time() > $result['time'] + $result['ttl'] ) {
            $this->delete($key);
            return false;
        }

        if ( !is_array($result) ) return false;
        return unserialize($result['data']);
    }

    public function delete($key) {
        return apc_delete($key);
    }

    public function info($id) {
        if ( !$result = apc_fetch($key) ) {
            return false;
        }
        return $result;
    }

    public function clear() {
        return apc_clear_cache('user');
    }
}