<?php
namespace yCrawler;


/**
 * Parser_Item_Modifier es una clase que define los modificadores que usan Parser_Item y Parser_Group
 * 
 * Sus métodos, estáticos, reciben un valor por referencia, y operan con él.<br />
 * En ocasiones también pueden recibir, y operar, con el propio Documento, que reciben también por referencia.
 * 
 * @todo: ¿tiene sentido que la clase sea instanciada? ¿podría ser abstracta?
 */
class Parser_Item_Modifier {
    
    const BOOLEAN_POSITIVE = true;
    const BOOLEAN_NEGATIVE = false;

    /**
     * Analiza si se ha recibido algo, y devuelve True o False en función del 'sentido' de decisión que se busque
     * 
     * Si se pretende un 'sentido positivo', devuelve True si se recibe algo, y False en caso contrario.
     * Si se pretende un 'sentido negativo', devuelve False si se recibe algo, y True en caso contrario.
     * 
     * @param Array $value (por referencia) Lo que se recibe
     * @param Document $document (por referencia) Documento
     * @param Boolean $sign (opcional) Indica el sentido de la 'decisión', por defecto, positiva (True)
     * @return Boolean 
     */
    public static function boolean(Array &$value = null, Document &$document, $sign = true) {      
        if ( (boolean)$value ) {
            $value = (boolean)$sign;
        } else {
            $value =! (boolean)$sign;
        }
        return true;
    }

    /**
     * Convierte a Enteros, los valores del Resultado que recibe por referencia
     * @param Array $value (por referencia) Resultado
     * @param Document $document (por referencia) Documento
     * @return Boolean True si se recibió algo, False si no
     */
    public static function int(Array &$value = null, Document &$document) {
        if ( !$value ) return false;
        foreach($value as &$data) {
            $data['value'] = (int)preg_replace("/[^0-9]/", '', $data['value']);
        }
        return true;
    }

    /**
     * Convierte a Flotantes, los valores del Resultado que recibe por referencia
     * @param Array $value (por referencia) Resultado
     * @param Document $document (por referencia) Documento
     * @return Boolean True si se recibió algo, False si no
     */
    public static function float(Array &$value = null, Document &$document) {
        if ( !$value ) return false;
        foreach($value as &$data) {
            $data['value'] = (float)str_replace(',','.', preg_replace("/[^0-9,.]/", '', $data['value']));
        }
        return true;
    }

    /**
     * Establece como valores del Resultado que recibe por referencia, los códigos HTML de sus 'nodes'
     * @param Array $value (por referencia) Resultado
     * @param Document $document (por referencia) Documento
     * @return Boolean True si se recibió algo, False si no
     */
    public static function html(Array &$value = null, Document &$document) {
        if ( !$value ) return false;
        foreach($value as &$data) $data['value'] = $data['dom']->saveXML($data['node']);
        return true;
    }

    /**
     * Establece como valores del Resultado que recibe por referencia, los códigos HTML de sus 'nodes' en los que los &lt;br&gt; se han sustituido por '\n\r'
     * @param Array $value (por referencia) Resultado
     * @param Document $document (por referencia) Documento
     * @return Boolean True si se recibió algo, False si no
     */
    public static function br2nl(Array &$value = null, Document &$document) {
        if ( !$value ) return false;
        foreach($value as &$data) $data['value'] = strip_tags(str_ireplace(Array('<br>','<br/>','<br />'),"\n\r", $data['dom']->saveXML($data['node'])));
        return true;
    }

    /**
     * Hace que el Resultado que recibe por referencia sea único, y que su valor sea un String suma de todos, unidos por un texto pasado
     * @param Array $value (por referencia) Resultado
     * @param Document $document (por referencia) Documento
     * @param String $glue (opcional) Cadena de caracteres que unen los diferentes valores del Resultado, por defecto : ' '
     * @return Boolean True si se recibió algo, False si no
     */
    public static function join(Array &$value = null, Document &$document, $glue = '') {
        if ( !$value ) return false;
        $output=Array();
        foreach($value as &$data) $output[] = $data['value'];
        $value = Array(Array(
            'value' => implode($glue, $output)
        ));
        return true;
    }

    /**
     * Convierte a timestamp UNIX, los valores del Resultado que recibe por referencia
     * @param Array $value (por referencia) Resultado
     * @param Document $document (por referencia) Documento
     * @return Boolean True si se recibió algo, False si no
     */
    public static function strtotime(Array &$value = null, Document &$document) {
        if ( !$value ) return false;
        foreach($value as &$data) $data['value'] = strtotime($data['value']);
    }

    /**
     * Convierte a timestamp UNIX, los valores del Resultado que recibe por referencia, tomándolos como si fueran los segundos a sumar al momento actual
     * @param Array $value (por referencia) Resultado
     * @param Document $document (por referencia) Documento
     * @param Document $miliseconds true, si se pasa el tiempo en milisegundos
     * @return Boolean True si se recibió algo, False si no
     */
    public static function timePlusNow(Array &$value = null, Document &$document, $miliseconds=false) {
        foreach($value as &$data) {
            $s=(int)$data['value'];
            $data['value'] = (!$miliseconds?$s:floor($s/1000))+time();
        }
        return true;
    }

    /**
     * Extrae las URL de las imágenes de los 'nodes' del Resultado pasado por referencia, y las pone como los valores del Resultado
     * @param Array $value (por referencia) Resultado
     * @param Document $document (por referencia) Documento
     * @return Boolean True si se recibió algo, False si no
     */
    public static function image(Array &$value = null, Document &$document) {
        if ( !$value ) return false;
        
        $output = Array();
        foreach($value as &$data) {
                    
            //Toma la imágen del SRC
            $img = $data['node']->getAttribute('src');
            //...si no, intenta del HREF
            if ( !Misc_URL::Image($img) ) { $img = $data['node']->getAttribute('href'); }
            //...si no, intenta del STYLE
            if ( !Misc_URL::Image($img) ) { 
                preg_match('~url\s*\([\'\"\s]*([^\'\"]+?)[\'\"\s]*\)~', $data['node']->getAttribute('style'), $_uri);
                if ( $_uri ) {
                    $img = $_uri[1];    
                }
            }
           
            //Resuelve rutas relativas y absolutas...
            $img=Misc_URL::URL($img,$document->getUrl());
            
            //Si, definitivamente, es una imágen, la almacena en la salida ($output)
            if ( Misc_URL::Image($img) ) {
                $output[]=$img;
            }       
            
        }

        $value = $output;
        return true;
    }

    /**
     * Ejecuta un Callback
     * 
     * El Callback recibirá por referencia, el Resultado, y el Documento
     * 
     * @param Array $value (por referencia) Resultado
     * @param Document $document (por referencia) Documento
     * @param Callable $callback
     * @return Mixed Valor devuelto por el Callback
     * @throws Exception En caso de que $callback sea "non-callable"
     */
    public static function callback(Array &$value = null, Document &$document, Array $callback) {
        if ( !is_callable($callback) ) {
            throw new Exception('Parser_Item_Modifier::callback: Unable to call a callback, non-callable.');
        }
 
        return call_user_func_array(
            $callback,
            Array(&$value, &$document)
        );
    }

}

