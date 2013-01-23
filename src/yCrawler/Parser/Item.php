<?php
namespace yCrawler;

/**
 * Parser_Item es una clase que compondrá Parser_Base, y define una regla concreta.
 * 
 * El objetivo de un Parser_Item, es definir una regla que Parser_Base pueda interpretar para obtener un resultado.
 * 
 * Los Parser_Item estarán definidos, básicamente, por una "regla", y un "tipo_de_regla"
 */
class Parser_Item {
    
    const TYPE_XPATH = 'xpath';
    const TYPE_REGEXP = 'regexp'; 
    const TYPE_LITERAL = 'literal';

    /**
     * Define la "regla" que se ha de cumplir en la evaluación del Parser_Item
     * @access private
     * @var String 
     */
    private $_pattern;
    
    /**
     * Define el "tipo_de_regla", o cómo se debe interpretar la "regla"
     * 
     * Las reglas pueden ser de los siguientes tipos:<br />
     * yCrawler\Parser_Item::TYPE_XPATH   -> Se interpreta como xPath (por defecto)<br />
     * yCrawler\Parser_Item::TYPE_REGEXP  -> Se interpreta como RegExp<br />
     * yCrawler\Parser_Item::TYPE_LITERAL -> Se interpreta literalmente

     * @default TYPE_XPATH
     * @access private
     * @var String 
     */
    private $_type = self::TYPE_XPATH;
    
    /**
     * Define el "atributo" del elemento DOM al que se refiere la "regla"
     * @access private
     * @var String 
     * @deprecated since version 2.0 Úsese la sintaxis propia de xPath para capturar atributos
     */
    private $_attribute;
    
    /**
     * Array de modificadores que se aplicarán sobre los resultados arrojados tras la interpretación de la "regla"
     * 
     * Un "MODIFICADOR" actúa modificando el resultado de la interpretación de la regla y el propio Documento.<br />
     * Los modificadores se aplicarán por el orden en que fueron creados, y en que figuran en $_modifier.
     * 
     * Un MODIFICADOR, tiene la sintáxis : Array(String nombre_modificador [, Mixed params]*)
     * 
     * Siendo:<br />
     *   String nombre_modificador , método estático : yCrawler\Parser_Item_Modifier::nombre_modificador<br />
     *   Mixed params , parámetros del MODIFICADOR
     * 
     * @access private
     * @var MODIFICADOR[]
     */
    private $_modifier;
    
    /**
     * Define la función que se aplicará tras la interpretación de la "regla"
     * 
     * Un CALLBACK tiene la sintaxis : Array(Parser_Item, String nombre_callback)
     * 
     * @access private
     * @var CALLBACK 
     * @deprecated since version 2.0 Úsese el modificador 'callback'
     */
    private $_callback;

    
    /**
     * Especifica el "tipo_de_regla"
     * @param String $type { 'xpath' | 'regexp' | 'literal' }
     * @return \yCrawler\Parser_Item
     */
    public function setType($type) {
        $this->_type = $type;
        return $this;
    }
 
    /**
     * Especifica la "regla" del Parser_Item
     * @param String $expression
     * @return \yCrawler\Parser_Item
     */
    public function setPattern($expression) {
        $this->_pattern = $expression;
        return $this;
    }

    /**
     * Define el "atributo" del elemento DOM al que se refiere la "regla"
     * @param String $attribute
     * @return \yCrawler\Parser_Item
     * @deprecated since version 2.0 Úsese la sintaxis propia de xPath para capturar atributos
     */
    public function setAttribute($attribute) {
        $this->_attribute = $attribute;
        return $this;
    }
    
    /**
     * Añade un modificador adicional, a la lista de los que puedan existir
     * 
     * Siendo:<br />
     *   String nombre_modificador , método estático : yCrawler\Parser_Item_Modifier::nombre_modificador<br />
     *   Mixed params , parámetros del MODIFICADOR
     * 
     * @param Args $modifier String nombre_modificador [, Mixed params]*
     * @return \yCrawler\Parser_Item
     * 
     * @see \yCrawler\Parser_Item_Modifier
     */
    public function setModifier($modifier) {
        //TODO: if(is_callable($modifier))
        //TODO: recurrencie en $modifier
        $args = func_get_args();
        array_shift($args);
        
        $this->_modifier[] = Array($modifier, $args);
        return $this;
    }

    /**
     * Define la función que se aplicará tras la interpretación de la "regla"
     * 
     * @param CALLBACK $callback Array(Parser_Item, String nombre_callback)
     * @return \yCrawler\Parser_Item
     * @throws Exception
     * @deprecated since version 2.0 Úsese el modificador 'callback'
     */
    public function setCallback(array $callback) {
        Output::log('Deprecated setCallback, use setModifier and callback modifier', Output::WARNING);
        if ( is_callable($callback) ) {
            $this->_callback = $callback;
        } else {
            throw new Exception('Unable to set a callback, non-callable.');
        }
        
        return $this;
    }

    /**
     * Devuelve el "tipo_de_regla" del Item
     * @return String
     */
    public function getType() { return $this->_type; }
    
    /**
     * Devuelve la "regla" del Item
     * @return String
     */
    public function getPattern() { return $this->_pattern; }
    
    /** 
     * Devuelve el atributo del DOM que pretende capturar la regla xPath
     * @return String
     * @deprecated since version 2.0 Úsese la sintaxis propia de xPath para capturar atributos
     */
    public function getAttribute() { return $this->_attribute; }
    
    /**
     * Devuelve los modificadores que haya solicitados en el Item
     * @return MODIFICADOR[] Array( Array(String nombre_modificador [, Mixed params]* )* )
     */
    public function getModifier() { return $this->_modifier; }
    
    /**
     * Devuelve la función que se aplicará tras la interpretación de la "regla"
     * @return CALLBACK Array(Parser_Item, String nombre_callback)
     * @deprecated since version 2.0 Úsese el modificador 'callback'
     */
    public function getCallback() { return $this->_callback; }

    /**
     * Aplica los modificadores, si los hay, en el orden en que se insertaron
     * 
     * Los modificadores actúan modificando, directamente, el Resultado del Parser_Group o el Documento
     * 
     * @access private
     * @param Array[][] $result (por referencia) Resultado de la interpretación de la "regla"
     * @param \yCrawler\Document $document (por referencia) Documento
     * @return true
     */
    private function applyModifier(&$result, Document &$document) {
        //si no hay modificadores, sale
        if ( !$this->_modifier ) return true;
        
        //si hay modificadores, va procesando uno a uno
        foreach( $this->_modifier as &$modifier) {
            //TODO: if(is_callable($modifier))...
            forward_static_call_array(
                Array(
                    'yCrawler\Parser_Item_Modifier',
                    $modifier[0]
                ),
                array_merge(Array(&$result, &$document), $modifier[1])
            );
        }

        return true;
    }
    
    /**
     * Aplica el Callback definido
     * @access private
     * @param Array[][] $result (por referencia) Resultado de la interpretación de la "regla"
     * @param \yCrawler\Document $document (por referencia) Document
     * @return true
     * @deprecated since version 2.0 Úsese el modificador 'callback'
     */
    private function applyCallback(&$result, Document &$document) {
    //TODO: Arreglar Callback, para pasar Document
        if ( !$this->_callback ) return true;
 
        return call_user_func_array(
            $this->_callback,
            Array(&$result, &$document)
        );
    }

    /**
     * Evalúa el Parser_Item contra un Documento, y devuelve un Resultado
     * 
     * En función del "tipo_de_regla" que aplique en el Parser_Item, la evaluación se hará con el método apropiado del Document.
     * 
     * @param \yCrawler\Document $document (por referencia) Documento sobre el que se evalúa el Parser_Item
     * @return Array[][] Resultado de la evaluación
     * 
     * @todo: Definir return<br />
     *   //-- error  : false<br />
     *   //-- nada   : (Array) NULL<br />
     *   //-- unico  : (Array){1}  ( (Array) ['value'=>String resultado_1] )<br />
     *   //-- varios : (Array){2,} ( (Array) ['value'=>String resultado_i]{2,} )<br />
     */
    public function evaluate(Document &$document) {
        switch($this->_type) {
            case self::TYPE_XPATH:
                $result = $document->evaluateXPath($this->_pattern, $this->_attribute);
                break;
            case self::TYPE_REGEXP:
                $result = $document->evaluateRegExp($this->_pattern);
                break;
            case self::TYPE_LITERAL:
                $result = Array(Array('value'=> $this->_pattern));
                break;
        }

        $this->applyCallback($result, $document); //TODO: @deprecated      
        $this->applyModifier($result, $document);
        

        return $result; 
    }

    /**
     * Representación en String del Parser_Item
     * @return String
     */
    public function __toString() {
        return '["' . $this->_pattern . '"][' . $this->_type . ']' . json_encode($this->_modifier);
    }
}