<?php
namespace yCrawler;

/**
 * Cache_Driver_File hace las veces de driver que gestiona la APC-Caché (no persistente)
 * 
 * No debe instanciarse directamente, sino que se accede a él a través de:
 * <code>
 * Cache::driver('APC')
 * </code>
 * 
 * @todo: ¿tiene sentido que la clase pueda ser instanciada? ¿podría ser abstracta?
 */
class Cache_Driver_APC implements Cache_Interface {
    
    /**
     * Guarda en APC-Cache un valor, bajo una clave, por un tiempo determinado
     * @param String $key Clave bajo la que se guardan los datos
     * @param Mixed $data Datos que se guardan
     * @param Integer $ttl (opcional) Tiempo (en segundos) que perdurarán los datos en APC-Cache, por defecto, no caducan
     * @return Boolean Si se pudo guardar en APC-Cache
     */
	public function set($key, $data, $ttl = 0) {
		$content = array(
			'time' => time(),
			'ttl' => $ttl,			
			'data' => serialize($data)
		);
		
		return apc_store((string)$key, $content, $ttl);
	}

    /**
     * Lee de APC-Cache los datos guardados bajo una clave; si caducó, lo borra
     * @param String $key Clave bajo la que se guardan los datos
     * @return Mixed Devuelve los datos guardados bajo la clave, o False si falló o ya caducaron
     */
	public function get($key) {
		if ( !$result = apc_fetch($key) ) {
			return false;
		}

		if ( $result['ttl'] > 0 && time() > $result['time'] + $result['ttl'] ) {
            //TODO: ¿No debería descomentarse para que se "caduquen"
            //$this->delete($key);
            //return false;
		}

		if ( !is_array($result) ) return false;
		return unserialize($result['data']);
	}
	
    /**
     * Elimina de la APC-Cache, los datos guardados bajo una clave
     * @param String $key Clave de la cual se eliminarán los datos guardados de la APC-Cache
     * @return Boolean Resultado del borrado
     */
	public function delete($key) {
		return apc_delete($key);
	}

    /**
     * Devuelve la meta-información guardada en la APC-Cache bajo una clave determinada
     * @param String $id Clave bajo la que se guardan los datos cuya meta-información se extraerá
     * @return Array Array con la información bajo las claves: 'time', 'ttl' y 'data'(serializada), o False si falló
     */
	public function info($id) {
		if ( !$result = apc_fetch($key) ) {
			return false;
		}
		return $result;
	}

    /**
     * Borra toda la APC-Cache
     * @return Boolean Resultado del borrado
     */
	public function clear() {
		return apc_clear_cache('user');
	}
}