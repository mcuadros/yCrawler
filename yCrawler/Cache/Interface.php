<?php
namespace yCrawler;

/**
 * Interface que define cómo deben ser los drivers que gestionan la Cache
 */
interface Cache_Interface {
    /**
     * Guarda en caché un valor, bajo una clave, por un tiempo determinado
     * @param String $key Clave bajo la que se guardan los datos
     * @param Mixed $data Datos que se guardan
     * @param Integer $ttl (opcional) Tiempo (en segundos) que perdurarán los datos en caché, por defecto, no caducan
     */
	public function set($key, $data, $ttl = 0);
    
    /**
     * Lee de caché los datos guardados bajo una clave
     * @param String $key Clave bajo la que se guardan los datos
     */
	public function get($key);
    
    /**
     * Elimina de la caché, los datos guardados bajo una clave
     * @param String $key Clave de la cual se eliminarán los datos guardados de la caché
     */
	public function delete($key);
    
    /**
     * Devuelve la meta-información guardada en la cache bajo una clave determinada
     * @param String $key Clave bajo la que se guardan los datos cuya meta-información se extraerá
     */
	public function info($key);
    
    /**
     * Borra toda la cache
     */
	public function clear();
}