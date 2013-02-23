<?php
namespace yCrawler\Cache\Driver;
use yCrawler\Cache\Driver;
use yCrawler\Crawler;
use yCrawler\Config;
use yCrawler\Base;

class File extends Base implements Driver {
    private $path;
    private $depth;

    public function __construct(Crawler $crawler) {
        parent::__construct($crawler);

        $this->path = Config::get('cache_path');
        $this->depth = Config::get('cache_folder_depth');
        $this->dir($this->path);
    }

    public function set($key, $data, $ttl = 0) {
        $content = array(
            'time' => time(),
            'ttl' => $ttl,          
            'data' => $data
        );
        
        return $this->writeFile((string)$key, $content);
    }

    public function get($key) {
        if ( !$result = $this->readFile($key) ) {
            return false;
        }

        if ( $result['ttl'] > 0 && time() > $result['time'] + $result['ttl'] ) {
            $this->delete($key);
            return false;
        }

        if ( !is_array($result) ) return false;
        return $result['data'];
    }
    
    public function delete($key) {
        return $this->deleteFile($key);
    }

    public function info($key) {
        if ( !$result = $this->readFile($key) ) {
            return false;
        }
        return $result;
    }

    public function clear() {
        $this->rmDir($this->path);
        $this->dir($this->path);
    }

    private function getPath($key, $justDir = false) {
        $base = $this->path;
        for($i=0;$i<$this->depth;$i++) $base .= substr($key, $i, 1) . '/';

        if ( $justDir ) return $base;
        return $base . $key;
    }

    private function deleteFile($key) {
        return unlink($this->getPath($key));
    }

    private function readFile($key) {
        $filename = $this->getPath($key);
        if ( !file_exists($filename) ) return false;
        
        return unserialize(file_get_contents($filename));
    }

    private function writeFile($key, $content) {
        $this->dir($this->getPath($key, true));
        return file_put_contents($this->getPath($key), serialize($content));
    }

    private function rmDir($dir) {
        foreach( glob($dir . '/*') as $file ) {
            if ( is_dir($file) ) $this->rmDir($file);
            else unlink($file);
        }

        return rmdir($dir);
    }

    private function dir($dir) {
        if ( !file_exists($dir) ) {
            if ( !mkdir($dir, 0700, true) ) {
                throw new Exception('Unable to create dir "' . $dir . '"');
            }
        }

        return true;
    }
}

