<?php
namespace yCrawler;

/**
 * Parser_Base es una clase abstracta que ha de ser extendida por una clase final a modo de 
 * configuración, donde se definirán todos los permenores para cada parser. 
 *  
 * <code>
 * namespace Config;
 * use yCrawler;
 *
 * class Localhost extends yCrawler\Parser_Base {
 *      public function initialize() {
 *          $this->setURLPattern('/localhost/');
 * 
 *          //config...
 *      }
 * }
 * </code>
 *
 */
abstract class Parser_Base extends Base {
    
    /**
     * Array de RegExp definidas que determinan si una URL puede ser parseada por este Parser
     * Si no se definió ninguna, se creará una acorde al dominio de las '_startup'
     * @access private
     * @var RegExp[] 
     */
    private $_urlPatterns = Array();
    
    /**
     * Array de las URLs que se parsearán al inicio del Parser
     * 
     * (OBLIGATORIO) Es necesario que haya al menos una
     * 
     * @access private
     * @var String[] 
     */
    private $_startup = Array();
    
    /**
     * Array multidimensional de los Parse_Item del Parser
     * 
     * Tiene la estructura:<br />
     * Array(<br />
     *   ['follow']=>Parse_Item[], //Si se extraen enlaces<br />
     *   ['verify']=>Parse_Item[], //Si se parsea el Documento<br />
     *   ['links']=>Parse_Item[], //Enlaces a extraer<br />
     *   ['values']=>Parse_Item[] //Info a parsear<br />
     * )
     * 
     * @access private
     * @var Array(Parse_Item[]) 
     */
    private $_items = Array();   
    
    /**
     * Flag que indica si el Parser ya ha sido iniciado 
     * @access private
     * @var Boolean 
     */
    private $_initialized = false;
    
    /**
     * Función que se ejecutará cuando se procese todo el Documento
     * @access private
     * @var Callable
     */
    private $_parseCallback;

    /**
     * Función que será ejecutada a modo de configuración del Parser.
     * Debe ser implementada por la clase final que extienda Parser_Base
     *  
     * <code>
     * namespace Config;
     * use yCrawler;
     *
     * class Localhost extends yCrawler\Parser_Base {
     *      public function initialize() {
     *          $this->setURLPattern('/localhost/');
     * 
     *          //config...
     *      }
     * }
     * </code>
     * 
     * @todo: [APROBAR] Declarar
     */
    public function initialize() { }
    
    /**
     * Inicializa (y configura) el Parser por primera vez, poniendo a true el flag '_initialized'
     * @return true
     * @throws Exception En caso de que no se haya implementado 'initialize()' en la clase final
     */
    public function configure() {
        if ( $this->_initialized ) return true;
        if ( !is_callable(Array($this, 'initialize')) ) {
            throw new Exception('Malformed Parser, initialize method not is defined or not callable.');
        }

        $this->initialize();
        return $this->_initialized = true;
    }

    /**
     * Añade una nueva URL a la lista de URL que visitará el Parser en su inicio
     * 
     * OBLIGATORIO: Debe definirse, al menos, una URL de inicio
     * 
     * @param String $url URL valida a añadir como URL de inicio
     * @param Boolean $clean (Opcional) Flag que de estar en 'true', borra las URLs de inicio ya configuradas.
     * @return String La URL de inicio añadida
     * @throws Exception En caso de que la URL indicada no sea válida
     */
    public function setStartupURL($url, $clean = false) {
        if ( $clean ) $this->_startup = Array();
        if ( !Misc_URL::validate($url) ) {
            throw new Exception('Unable to set startup URL, non-valid url. ' . $url);
        }
        
        return $this->_startup[] = $url;
    }

    /**
     * Añade una nueva RegExp que determina si una URL, recuperada de un Documento, puede ser parseada por este Parser, o si debe desecharse.
     * 
     * OPCIONAL: Si no se define ninguna RegExp, se crearán acorde a los dominios de las '_startup'
     * 
     * @param String $regexp Expresión regular que validará las URL capturadas
     * @param Boolean $clean (Opcional) Flag que de estar en 'true', borra las RegExp ya configuradas.
     * @return String La RegExp añadida
     */
    public function setURLPattern($regexp, $clean = false) {
        if ( $clean ) $this->_urlPatterns = Array();
        if ( preg_match($regexp, '') === false ) return false;    
        return $this->_urlPatterns[] = $regexp;
    }

    /**
     * Instancia y asocia al documento, bajo _item['follow'], un nuevo Parser_Item para decidir si esta pagina sera indexada
     * 
     * El sentido de la regla, sobre indexar o no una página, viene determinado por 'Boolean $sign'.<br />
     * - Si $sign es True, sólo se seguirán los enlaces del Documento si la evaluación del Parser_Item devuelve resultado
     * - Si $sign es False, se ignorarán todos los enlaces del Documento si la evaluación del Parser_Item devuelve resultado
     * 
     * OPCIONAL: Si no se especifica, todas las páginas serán indexadas.
     * 
     * @param String $expression Expresión xPath o RegExp que configurará la "regla" del Parser_Item
     * @param Boolean $sign (opcional) Indica el sentido de la regla, por defecto, positiva (True)
     * @return Parser_Item Devuelve una referencia al mismo Parser_Item creado
     */
    public function createLinkFollowItem($expression = false, $sign = true) {
        $item = new Parser_Item();
        if ( $expression ) $item->setPattern($expression);
        $this->_items['follow'][] = Array(&$item, $sign);
        return $item;
    }

    /**
     * Instancia y asocia al documento, bajo _items['verify'], un Parser_Item para decidir si el Documento será parseado
     * 
     * El sentido de la regla, sobre parsear o no una página, viene determinado por 'Boolean $sign'.<br />
     * - Si $sign es True, sólo se parseará el Documento si la evaluación del Parser_Item devuelve resultado
     * - Si $sign es False, no se parseará el Documento si la evaluación del Parser_Item devuelve resultado
     * 
     * OPCIONAL: Si no se definine nada no se parseará ninguna pagina.
     * 
     * @param String $expression Expresión xPath o RegExp que configurará la "regla" del Parser_Item
     * @param Boolean $sign (opcional) Indica el sentido de la regla, por defecto, positiva (True)
     * @return Parser_Item Devuelve una referencia al mismo Parser_Item creado
     */
    public function createVerifyItem($expression = false, $sign = true) {
        $item = new Parser_Item();
        if ( $expression ) $item->setPattern($expression);
        $this->_items['verify'][] = Array(&$item, $sign);
        return $item;
    }

    /**
     * Instancia y asocia al documento, bajo _items['links'], un Parser_Item para la extracción de enlaces.
     * 
     * OPCIONAL: En caso de no asignar ninguno se creara automaticamente uno basico (//a)
     * 
     * @param string $expression Expresión xPath o RegExp que capturará los enlaces
     * @return Parser_Item Devuelve una referencia al mismo Parser_Item creado
     */
    public function createLinksItem($expression = false) { 
        $item = new Parser_Item();
        if ( $expression ) $item->setPattern($expression);
        $this->_items['links'][] = &$item;
        return $item;
    }

    /**
     * Instancia y asocia al documento, bajo _items['values'], un Parser_Item para la extracción de un valor por parseo del Documento
     *
     * OPCIONAL: En caso de no asignar ninguno no se obtendrá ningún valor como resultado del parseo
     * 
     * @param String $name Nombre vajo el que se guardará el resultado del análisis del Parser_Item
     * @param String $expression Regla del Parser_Item
     * @return Devuelve una referencia al mismo Parser_Item creado
     */
    public function createValueItem($name, $expression = false) {
        $item = new Parser_Item();
        if ( $expression ) $item->setPattern($expression);
        $this->_items['values'][$name] = &$item;
        return $item;
    }

    /**
     * Instancia y asocia al documento, bajo _items['values'], un Parser_Group, conjunto de Parser_Item
     * @param String $name Nombre vajo el que se guardará el resultado del análisis del Parser_Item
     * @return Devuelve una referencia al mismo Parser_Group creado
     */
    public function createValueGroup($name) {
        $group = new Parser_Group();
        $this->_items['values'][$name] = &$group;
        return $group;
    }

    /**
     * Especifica un Callback que se ejecutará cuando se procese el Documento
     * @param Callable $callback Array-callable
     * @return True
     * @throws Exception Si se intenta guardar un non-callable
     */
    public function setParseCallback(Array $callback) {
        if ( !is_callable($callback) ) {
            throw new Exception('Parser_Base::setParseCallback: Unable to call a callback, non-callable.');
        }
        $this->_parseCallback = &$callback;
        return true;
    }

    /**
     * Ejecuta el Callback del Parser_Base
     * @access protected
     * @param Document $document (por referencia) Documento sobre el que se aplicará el Calback
     * @return Mixed Devuelve el valor devuelto por la función Callback
     * 
     * @todo: ¿No convendría pasar el Documento por referencia?
     */
    public function parseCallback(Document &$document) {
        if ( !$this->_parseCallback ) return null;

        return call_user_func_array(
            $this->_parseCallback,
            Array(&$document)
        );
    }
    
    /**
     * Devuelve un Array con las URL de inicio del Parser_Base
     * @return String[]
     */
    public function getStartupURLs() { return $this->_startup; }
    
    /**
     * Devuelve un Array con las RegExp de validación de URLs capturadas, del Parser_Base
     * @return RegExp[]  
     */
    public function getURLPatterns() { return $this->_urlPatterns; }

    /**
     * Devuelve un Array de los Parser_Item que definen las reglas de seguimiento de enlaces
     * @return Parser_Item[] 
     */
    public function &getFollowItems() { 
        if ( !$return = array_key_exists('follow', $this->_items) ) { return $return; }
        return $this->_items['follow']; 
    }

    /**
     * Devuelve un Array de los Parser_Item que definen las reglas de extracción de enlaces
     * @return Parser_Item[] 
     */
    public function &getLinksItems() { 
        if ( !$return = array_key_exists('links', $this->_items) ) { return $return; }
        return $this->_items['links']; 
    }

    /**
     * Devuelve un Array de los Parser_Item que definen las reglas de validación para el parseo del Documento
     * @return Parser_Item[] 
     */
    public function &getVerifyItems() { 
        if ( !$return = array_key_exists('verify', $this->_items) ) { return $return; }
        return $this->_items['verify']; 
    }

    /**
     * Devuelve un Array de los Parser_Item que definen las reglas de parseo para la extracción de resultados
     * @return Parser_Item[] 
     */
    public function &getValueItems() { 
        if ( !$return = array_key_exists('values', $this->_items) ) { return $return; }
        return $this->_items['values']; 
    }

    /**
     * Dada una URL determina si esta puede ser parseada o no por este parser.
     * 
     * Se considera que el Documento puede ser parsead si existe al menos una RegExp que valide la URL pasada.
     * 
     * Si no existe ninguna RegExp en _urlPatterns, crea una que valide enlaces que tengan el mismo PHP_URL_HOST
     * 
     * @param String $url
     * @return Boolean Devuelve si la URL puede ser parseada o no
     */
    public function matchURL($url) {
        if ( count($this->_urlPatterns) == 0 ) {
            $tmp = Array();
            foreach($this->_startup as $url) {
                $domain = parse_url($url, PHP_URL_HOST);
                $tmp[] = '~^https?://' . str_replace('.', '\.', $domain) . '~';
            }
            $this->_urlPatterns = array_unique($tmp);
        }

        foreach($this->_urlPatterns as $regexp) {
            if ( preg_match($regexp, $url) === 1 ) return true;
        } 

        return false;
    }
}

