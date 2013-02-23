<?php
namespace yCrawler\Parser;
use yCrawler\Document;

class Item {
    const TYPE_XPATH = 'xpath';
    const TYPE_REGEXP = 'regexp'; 
    const TYPE_LITERAL = 'literal';

    private $pattern;
    private $type = self::TYPE_XPATH;
    private $attribute;
    private $modifier;
    private $callback;

    
    /**
     * Especifica el "tipo_de_regla"
     * @param String $type { 'xpath' | 'regexp' | 'literal' }
     * @return \yCrawler\Parser_Item
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }
 
    /**
     * Especifica la "regla" del Parser_Item
     * @param String $expression
     * @return \yCrawler\Parser_Item
     */
    public function setPattern($expression) {
        $this->pattern = $expression;
        return $this;
    }

    /**
     * Define el "atributo" del elemento DOM al que se refiere la "regla"
     * @param String $attribute
     * @return \yCrawler\Parser_Item
     * @deprecated since version 2.0 Úsese la sintaxis propia de xPath para capturar atributos
     */
    public function setAttribute($attribute) {
        $this->attribute = $attribute;
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
        
        $this->modifier[] = Array($modifier, $args);
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
            $this->callback = $callback;
        } else {
            throw new Exception('Unable to set a callback, non-callable.');
        }
        
        return $this;
    }

    /**
     * Devuelve el "tipo_de_regla" del Item
     * @return String
     */
    public function getType() { return $this->type; }
    
    /**
     * Devuelve la "regla" del Item
     * @return String
     */
    public function getPattern() { return $this->pattern; }
    
    /** 
     * Devuelve el atributo del DOM que pretende capturar la regla xPath
     * @return String
     * @deprecated since version 2.0 Úsese la sintaxis propia de xPath para capturar atributos
     */
    public function getAttribute() { return $this->attribute; }
    
    /**
     * Devuelve los modificadores que haya solicitados en el Item
     * @return MODIFICADOR[] Array( Array(String nombre_modificador [, Mixed params]* )* )
     */
    public function getModifier() { return $this->modifier; }
    
    /**
     * Devuelve la función que se aplicará tras la interpretación de la "regla"
     * @return CALLBACK Array(Parser_Item, String nombre_callback)
     * @deprecated since version 2.0 Úsese el modificador 'callback'
     */
    public function getCallback() { return $this->callback; }

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
        if ( !$this->modifier ) return true;
        
        //si hay modificadores, va procesando uno a uno
        foreach( $this->modifier as &$modifier) {
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
        if ( !$this->callback ) return true;
 
        return call_user_func_array(
            $this->callback,
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
        $result = array();

        switch($this->type) {
            case self::TYPE_XPATH:
                $result = $document->evaluateXPath($this->pattern, $this->attribute);
                break;
            case self::TYPE_REGEXP:
                $result = $document->evaluateRegExp($this->pattern);
                break;
            case self::TYPE_LITERAL:
                $result = Array(Array('value'=> $this->pattern));
                break;
        }

        $this->applyCallback($result, $document); //TODO: @deprecated      
        $this->applyModifier($result, $document);
        
        if ( !$result ) return array();
        return $result; 
    }

    /**
     * Representación en String del Parser_Item
     * @return String
     */
    public function __toString() {
        return '["' . $this->pattern . '"][' . $this->type . ']' . json_encode($this->modifier);
    }
}