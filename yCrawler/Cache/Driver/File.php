<?php
namespace yCrawler;

/**
 * Cache_Driver_File hace las veces de driver que gestiona la Caché en archivos (persistente)
 * 
 * No debe instanciarse directamente, sino que se accede a él a través de:
 * <code>
 * Cache::driver('APC')
 * </code>
 * 
 * @todo: ¿tiene sentido que la clase pueda ser instanciada? ¿podría ser abstracta?
 */
class Cache_Driver_File implements Cache_Interface {
    
    /**
     * Directorio donde se guardara la cache
     * @access private
     * @var String 
     */
	private $_path;
    
    /**
     * Profundidad de carpetas de cache
     * @access private
     * @var Int 
     */
	private $_depth;

    /**
     * El constructor comprueba que existe la carpeta principal del cache, para crearla, o lanzar Excepción si no se puede crear.
     */
	public function __construct() {
        $this->_path = Config::get('cache_path');
        $this->_depth = Config::get('cache_folder_depth');
        
		$this->dir($this->_path);
	}

    /**
     * Guarda en caché un valor, bajo una clave, por un tiempo determinado
     * @param String $key Clave bajo la que se guardan los datos
     * @param Mixed $data Datos que se guardan
     * @param Integer $ttl (opcional) Tiempo (en segundos) que perdurarán los datos en caché, por defecto, no caducan
     * @return Boolean Si se pudo guardar en caché
     */
	public function set($key, $data, $ttl = 0) {
		$content = array(
			'time' => time(),
			'ttl' => $ttl,			
			'data' => $data
		);
		
		return $this->writeFile((string)$key, $content);
	}

    /**
     * Lee de caché los datos guardados bajo una clave; si caducó, lo borra
     * @param String $key Clave bajo la que se guardan los datos
     * @return Mixed Devuelve los datos guardados bajo la clave, o False si falló o ya caducaron
     */
	public function get($key) {
		if ( !$result = $this->readFile($key) ) {
			return false;
		}

		if ( $result['ttl'] > 0 && time() > $result['time'] + $result['ttl'] ) {
            //TODO: ¿No debería descomentarse para que se "caduquen"
            //$this->delete($key);
            //return false;
		}

		if ( !is_array($result) ) return false;
		return $result['data'];
	}
	
    /**
     * Elimina de la caché, los datos guardados bajo una clave
     * @param String $key Clave de la cual se eliminarán los datos guardados de la caché
     * @return Boolean Resultado del borrado
     */
	public function delete($key) {
		return $this->deleteFile($key);
	}

    /**
     * Devuelve la meta-información guardada en la cache bajo una clave determinada
     * @param String $key Clave bajo la que se guardan los datos cuya meta-información se extraerá
     * @return Array Array con la información bajo las claves: 'time', 'ttl' y 'data', o False si falló
     */
	public function info($key) {
		if ( !$result = $this->readFile($key) ) {
			return false;
		}
		return $result;
	}

    /**
     * Borra toda la cache y el directorio $this->_folder
     * @return Boolean Resultado del borrado
     */
	public function clear() {
		return $this->rmDir($this->_path);
	}

    /**
     * Obtiene la ruta donde se almacena un dato identificado por una clave concreta
     * @access private
     * @param String $key Clave bajo la que se busca la ruta
     * @param Boolean $justDir (opcional) Indica si se quiere sólo la ruta del directorio que aloja el dato, por defecto, da la ruta del archivo del dato
     * @return String Ruta bajo la que se encuentra en caché el dato
     */
	private function getPath($key, $justDir = false) {
		$base = $this->_path . '/';
		for($i=0;$i<$this->_depth;$i++) $base .= substr($key, $i, 1) . '/';

		if ( $justDir ) return $base;
		return $base . $key;
	}

    /**
     * Borra de la caché un dato identificado por una clave
     * @access private
     * @param String $key Clave de la cual se eliminarán los datos guardados de la caché
     * @return Boolean Si se pudo borrar
     */
	private function deleteFile($key) {
		return unlink($this->getPath($key));
	}

    /**
     * Lee (y deserializa) un dato de la caché, identificado por su clave
     * @access private
     * @param String $key Clave de la cual se leerán los datos guardados de la caché
     * @return Mixed devuelve el dato bajo la clave, o False si no se  encontró
     */
	private function readFile($key) {
		$filename = $this->getPath($key);
		if ( !file_exists($filename) ) return false;
		
		return unserialize(file_get_contents($filename));
	}

    /**
     * Escribe en caché (serializado) un dato, bajo una clave
     * @access private
     * @param String $key Clave de la cual se guardarán los datos en caché
     * @param Mixed $content Dato a guardar en caché
     * @return Boolean Si se consiguió guardar en caché
     */
	private function writeFile($key, $content) {
		$this->dir($this->getPath($key, true));
		return file_put_contents($this->getPath($key), serialize($content));
	}

    /**
     * Borra un directorio completo
     * @access private
     * @param String $dir Directorio a borrar
     * @return Boolean Si consiguió borrarlo 
     */
	private function rmDir($dir) {
		foreach( glob($dir . '/*') as $file ) {
        	if ( is_dir($file) ) $this->rmDir($file);
        	else unlink($file);
    	}

    	return rmdir($dir);
	}

    /**
     * Verifica que exista un directorio, y lo crea si no existe.
     * @access private
     * @param String $dir
     * @return Boolean True
     * @throws Exception Si no consigue crear el directorio
     */
	private function dir($dir) {
		if ( !file_exists($dir) ) {
			if ( !mkdir($dir, 0700, true) ) {
				throw new Exception('Unable to create dir "' . $dir . '"');
			}
		}

		return true;
	}
}

