<?php
namespace yCrawler;
use yCrawler\Cache\Driver;

class Cache {
	private $drivers = Array();

    public function add(Driver $driver) {
        $tmp = explode('\\', get_class($driver));
        $name = end($tmp);

        if ( $this->hasDriver($name) ) {
            throw new \RuntimeException(sprintf('Allready load a cache driver "%s"', $name));
        }

        $this->drivers[$name] = $extension;

        return true;
    }

    public function hasDriver($name) {
        return isset($this->drivers[$name]);
    }

	public function get($name) {
		$class = 'yCrawler\Cache_Driver_' . $name;
		if ( !array_key_exists($class, self::$drivers) ) {
			return self::loadDriver($class);
		}
		return self::$drivers[$class];
	}
}