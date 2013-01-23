<?php
namespace yCrawler;

/**
 * Singleton que ofrece los drivers únicos para usar la caché
 * 
 * Para acceder a los drivers que gestionan la Cache se usa:
 * <code>
 * Cache::driver('nombre_driver')
 * </code>
 * 
 * @todo: ¿tiene sentido que la clase pueda ser instanciada? ¿podría ser abstracta?
 */
class Cache {
    
    /**
     * Colección de Drivers para acceder a la caché, indexados por nombre
     * @todo: ¿no debería ser privada?
     * 
     * @var Driver<Cache_Interface>[] Array de drivers de acceso a la caché, que implementan Cache_Interface
     */
	static $drivers = Array();

    /**
     * Instancia un driver, verificando que implemente Cache_Interface
     * 
     * @todo: ¿no debería ser privada?
     * 
     * @param String $class Nombre del driver (debe implementar 'yCrawler\Cache_Interface')
     * @return Driver<Cache_Interface> Driver de acceso a la caché, que implementa Cache_Interface
     * @throws Exception En caso de que el driver no implemente Cache_Interface
     */
	public static function loadDriver($class) {
		$rClass = new \ReflectionClass($class);
		if ( !$rClass->implementsInterface('yCrawler\Cache_Interface') ) {
			throw new Exception('The driver class must implements Cache_Interface interface');
		}
		return self::$drivers[$class] = new $class;
	}

    /**
     * Devuelve acceso al Driver
     * 
     * Este es el punto de acceso a los drivers que gestionan la Cache
     * 
     * @param String $name <NOMBRE> del driver (Debe existir como 'yCrawler\Cache_Driver_<NOMBRE>')
     * @return Driver<Cache_Interface> Driver de acceso a la caché, que implementa Cache_Interface 
     * @throws Exception En caso de que el driver no implemente Cache_Interface
     */
	public static function driver($name) {
		$class = 'yCrawler\Cache_Driver_' . $name;
		if ( !array_key_exists($class, self::$drivers) ) {
			return self::loadDriver($class);
		}
		return self::$drivers[$class];
	}
}