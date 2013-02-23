<?php
namespace yCrawler;

/**
 * Document es  
 */
class Document extends Request {
    protected $parser;
    protected $dom;
    protected $xpath;

    protected $values = Array();
    protected $links = Array();

    protected $verified;
    protected $indexable;
    protected $parsed;

    public function __construct($url, Parser $parser = null) {
        parent::__construct($url);
        return $this->parser = $parser;
    }

    protected function makeRequest() {
        if ( $this->getStatus() >= Request::STATUS_DONE ) return true;
        if ( $this->getStatus() != Request::STATUS_NONE ) return false;

        if ( !$this->call() ) return false;
        if ( !$this->createDOM() ) return false;
        if ( !$this->createXPath() ) return false;
        return true;
    }

    protected function createDOM() {
        libxml_use_internal_errors(true); 
        libxml_clear_errors();

        $this->dom = new \DOMDocument(); 
        
        if ( Config::get('utf8_dom_hack') ) {
            return $this->dom->loadHtml(sprintf(
                '<?xml encoding="UTF-8">%s</xml>',
                str_ireplace('utf-8','', $this->getResponse())
            ));
        }

        return $this->dom->loadHtml($this->getResponse());
    }

    protected function createXPath() {
        $this->xpath = new \DOMXPath($this->dom); 
        if ( $this->xpath ) { return true; } else { return false; }
    }

    public function getParser() { return $this->parser; }
    public function setParser(Parser $parser) { 
        return $this->parser = $parser;
    }

    public function getLinks() { return $this->links; }
    public function addLink($url, $override = false) {
        if( $override ) $this->links = array($url);
        else $this->links[] = $url;
    
        $this->links = array_values(array_unique($this->links));
        $this->data('set', 'links', count($this->links));
        return $url;
    }

    public function getValues() { return $this->values; }
    public function getValue($name) { 
        if ( !isset($this->value[$name]) ) return false; 
        return $this->values[$name]; 
    }

    public function addValue($name, $data, $override = false) {
        $this->data('add', 'values');
        if ( $override ) $this->values[$name] = Array();
        if ( is_array($data) )  return $this->values[$name][] = $data['value'];
        return $this->values[$name][] = $data;
    }
    
    /**
     * Verifica si este documento es objetivo del parseo.
     * @return boolean
     */
    public function isVerified() {
        if ( $this->verified === null ) {
            $this->verified = $this->verify();
            $this->data('set', 'verified', $this->verified);
        }
    
        return $this->verified;
    }
    
    public function isIndexable() {
        if ( $this->indexable === null ) {
            $this->indexable = $this->follow();
            $this->data('set', 'indexable', $this->indexable);
    }

        return $this->indexable;
    }
    
    protected function verify() {
        if ( !$this->makeRequest() ) return false; 
        if ( !$verifyItems = $this->parser->getVerifyItems() ) return false;

        foreach($verifyItems as &$item) {
            $item[0]->setModifier('boolean', $item[1]);
            if ( !$item[0]->evaluate($this) ) return false;
        }

        return true;
    }
    /**
     * Verifica si este documento es indexable.
     * @return boolean
     */
    protected function follow() {
        if ( !$this->makeRequest() ) return false;
        if ( !$followItems = $this->parser->getFollowItems() ) return true;

        foreach($followItems as &$item) {
            $item[0]->setModifier('boolean', $item[1]);
            if ( !$item[0]->evaluate($this) ) return false;
        }

        return true;
    }

    /**
     * Extrae todos los valores definidos en el parser, del documento dado y
     * los almacena en el documento. Devuelve un array con los dato extraidos.
     * @return array
     */
    public function evaluate() {
        if ( !$this->isVerified() ) return false;

        $valueItems = (Array)$this->parser->getValueItems();
        foreach($valueItems  as $name => &$item) { 
            foreach((Array)$item->evaluate($this) as $data) $this->addValue($name, $data);
        }

        return $this->getValues();
    }

    /**
     * Extrae todos los enlaces siguiendo las reglas definidas en el parser, del 
     * documento dado y los almacena en el documento. Devuelve un array con los 
     * enlaces.
     * @return array
     */
    public function links() {
        if ( !$this->isIndexable() ) return false;

        if ( !$this->parser->getLinksItems() ) {
            $this->parser->createLinksItem('//a')->setAttribute('href');
        }

        $linksItems = (Array)$this->parser->getLinksItems();
        foreach($linksItems as &$item) { 
            foreach((Array)$item->evaluate($this) as $data) {
                $url = Misc_URL::URL($data['value'], $this->getUrl());
                if ( $url && $this->parser->matchURL($url) ) $this->addLink($url);
            }
        }

        return $this->getLinks();
    }
    
    /**
     * Realiza un evaluate() y un links() sobre el documento.
     * @return Document
     */
    public function parse() {
        if ( $this->parsed ) return $this;
        if ( $this->parser->configure() ) {
            $this->links();
            $this->evaluate();

            $this->parsed = true;
            $this->parser->parseCallback($this);
        }    

        return $this;
    }

    public function evaluateXPath($expression, $attribute = false) {
        if ( !$this->makeRequest() ) return false; 

        $output = Array();
        $result = $this->xpath->evaluate($expression);
        
        if ( !$result ) return null;
        if ( $result->length == 0 )  return null;

        foreach ($result as $node) {
            if (!$attribute) {
                $output[] = Array(
                    'value' => $node->nodeValue,
                    'node' => $node,
                    'dom' => &$this->dom   
                );
            } else {
                $output[] = Array(
                    'value' =>  $node->getAttribute($attribute)   
                );
            }
        }

        if ( count($output) == 0 ) return null;
        return $output;
    }

    //TODO: preg_match_all
    public function evaluateRegExp($expression) {
        if ( !$this->makeRequest() ) return false; 
         
        if ( !preg_match($expression, $this->getResponse(), $matches) ) return false;

        if ( count($matches) != 0 ) { 
            return Array(
                Array('value' => $matches[count($matches)-1])
            ); 
        } else {
            return false; 
        }
    }      
}