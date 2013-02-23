<?php
namespace yCrawler\Parser;
use yCrawler\Parser\Item;

/**
 * Parser_Group es una clase que compondrá Parser_Base, y a su vez, es una composición de Parser_Item.
 * 
 * El objetivo de un Parser_Group, es definir una composición de Parser_Item, que Parser_Base pueda
 * interpretar para obtener un único resultado.
 */
class Group {
    
    /**
     * Array de objetos Parser_Item, cuya interpretación definirán el resultado de la evaluación del Parser_Group
     * @access private
     * @var Parser_Item[] 
     */
    private $_items = Array();
    
    /**
     * Array de modificadores que se aplicarán sobre los resultados arrojados tras la interpretación del Parser_Group
     * 
     * Un "MODIFICADOR" actúa modificando el resultado de la interpretación del Parser_Group, y el propio Documento<br />
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
     * Instancia y añade al Parser_Group, un nuevo Parser_Item
     * @param String $name Nombre del nuevo Parser_Item
     * @param String $expression (opcional) Define la "regla" que se ha de cumplir en la evaluación del Parser_Item
     * @return \yCrawler\Parser_Item 
     */
    public function &createItem($name, $expression = false) {
        $item = new Parser_Item();
        if ( $expression ) $item->setPattern($expression);

        $this->_items[] = &$item;
        return $item;
    }

    /**
     * Evalúa cada uno de los Parser_Item del ParseGroup contra un Documento, y devuelve un Resultado como unión de
     * todos los resultados de cada Parser_Item
     * 
     * @todo: Definir return<br />
     *   //-- error  : false<br />
     *   //-- nada   : (Array) NULL<br />
     *   //-- unico  : (Array){1}  ( (Array) ['value'=>String resultado_1] )<br />
     *   //-- varios : (Array){2,} ( (Array) ['value'=>String resultado_i]{2,} )<br />
     * 
     * @param \yCrawler\Document $document (por referencia) Documento sobre el que se evalúan los Parser_Item
     * @return Array[][] Resultado de la evaluación
     */
    public function evaluate(Document &$document) {
        $output = Array();
        foreach($this->_items as &$item) {
            foreach($item->evaluate($document) as $data) $output[] = $data;
        }
                
        $this->applyModifier($output, $document);
        
        return $output;
    }
    
    /**
     * Representación en String del Parser_Group
     * @return String
     */
    public function __toString() {
        return (string)var_export($this);
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
     * @todo: [APROBAR] permitir modificadores
     */
    public function setModifier($modifier) {
        $args = func_get_args(); 
        array_shift($args);
        
        $this->_modifier[] = Array($modifier, $args);
        return $this;
    }
    
    /**
     * Devuelve los modificadores que haya solicitados en el Parser_Group
     * @return MODIFICADOR[] Array( Array(String nombre_modificador [, Mixed params]* )* )
     * 
     * @todo: [APROBAR] permitir modificadores
     */
    public function getModifier() { return $this->_modifier; }
    
    /**
     * Aplica los modificadores, si los hay, en el orden en que se insertaron
     * 
     * Los modificadores actúan modificando, directamente, el Resultado del Parser_Group o el Documento
     * 
     * @access private
     * @param Array[][] $result (por referencia) Resultado de la interpretación del Parser_Group
     * @param \yCrawler\Document $document (por referencia) Documento
     * @return true
     * 
     * @todo: [APROBAR] permitir modificadores
     */ 
    private function applyModifier(&$result, Document &$document) {
        //si no hay modificadores, sale
        if ( !$this->_modifier ) return true;
        
        //si hay modificadores, va procesando uno a uno
        foreach( $this->_modifier as &$modifier) {
            //TODO: if(is_callable($modifier))... g  
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
    
    
    
}