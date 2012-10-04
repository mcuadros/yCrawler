<?php
namespace yCrawler;

/**
 * Document es  
 */
class Document extends Request {
    protected $_parser;
    protected $_dom;
    protected $_xpath;

    protected $_values = Array();
    protected $_links = Array();

    protected $_verified;
    protected $_indexable;
    protected $_parsed;

    public function __construct($url, Parser_Base &$parser) {
        parent::__construct($url);
        return $this->_parser = &$parser;
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
        //We dont want get XML errors, bye bye
        libxml_use_internal_errors(true); 
        libxml_clear_errors();

        $this->_dom = new \DOMDocument(); 
        
        if ( Config::get('utf8_dom_hack') ) {
            return $this->_dom->loadHtml('<?xml encoding="UTF-8">' . str_ireplace('utf-8','', $this->getResponse()));
        }

        return $this->_dom->loadHtml($this->getResponse());
    }

    protected function createXPath() {
        $this->_xpath = new \DOMXPath($this->_dom); 
        if ( $this->_xpath ) { return true; } else { return false; }
    }

    public function &getParser() { return $this->_parser; }
    public function setParser(Parser_Base &$parser) { 
        return $this->_parser = &$parser;
    }

    public function getLinks() { return $this->_links; }
    public function addLink($url) {
        $this->_links[] = $url;
        $this->_links = array_values(array_unique($this->_links));
        $this->data('set', 'links', count($this->_links));
        return $url;
    }

    public function getValues() { return $this->_values; }
    public function getValue($name) { 
        if ( !array_key_exists($name, $this->_values) ) return false; 
        return $this->_values[$name]; 
    }

    public function addValue($name, $data, $override = false) {
        $this->data('add', 'values');
        if ( $override ) $this->_values[$name] = Array();
        if ( is_array($data) )  return $this->_values[$name][] = $data['value'];
        return $this->_values[$name][] = $data;
    }
    
    /**
     * Verifica si este documento es objetivo del parseo.
     * @return boolean
     */
    public function isVerified() {
        if ( $this->_verified === null ) {
            $this->_verified = $this->verify();
            $this->data('set', 'verified', $this->_verified);
        }
    
        return $this->_verified;
    }
    
    public function isIndexable() {
        if ( $this->_indexable === null ) {
            $this->_indexable = $this->follow();
            $this->data('set', 'indexable', $this->_indexable);
    }

        return $this->_indexable;
    }
    
    protected function verify() {

        if ( !$this->makeRequest() ) return false; 
        if ( !$verifyItems = $this->_parser->getVerifyItems() ) return false;

        foreach($verifyItems as &$item) {
            $item[0]->setModifier('boolean', $item[1]);
            if ( !$item[0]->evaluate($this) ) {
                Output::log('Varification negative: ' . $item[0], Output::DEBUG);
                return false;
            }
        }

        return true;
    }
    /**
     * Verifica si este documento es indexable.
     * @return boolean
     */
    protected function follow() {
        if ( !$this->makeRequest() ) return false;
        if ( !$followItems = $this->_parser->getFollowItems() ) return true;

        foreach($followItems as &$item) {
            $item[0]->setModifier('boolean', $item[1]);
            if ( !$item[0]->evaluate($this) ) {
                Output::log('Follow negative: ' . $item[0], Output::DEBUG);
                return false;
            }
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

        $valueItems = (Array)$this->_parser->getValueItems();
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


        if ( !$this->_parser->getLinksItems() ) {
            $this->_parser->createLinksItem('//a')->setAttribute('href');
        }

        $linksItems = (Array)$this->_parser->getLinksItems();
        foreach($linksItems as &$item) { 
            foreach((Array)$item->evaluate($this) as $data) {
                $url = Misc_URL::URL($data['value'], $this->getUrl());
                if ( $url && $this->_parser->matchURL($url) ) $this->addLink($url);
            }
        }

        return $this->getLinks();
    }
    
    /**
     * Realiza un evaluate() y un links() sobre el documento.
     * @return Document
     */
    public function &parse() {
        if ( $this->_parsed ) return $this;
        if ( $this->_parser->configure() ) {
            $this->links();
            $this->evaluate();

            $this->_parsed = true;
            $this->_parser->parseCallback($this);
        }    

        return $this;
    }

    public function evaluateXPath($expression, $attribute = false) {
        if ( !$this->makeRequest() ) return false; 

        $output = Array();
        $result = $this->_xpath->evaluate($expression);
        
        if ( !$result ) return null;
        if ( $result->length == 0 )  return null;

        foreach ($result as $node) {
            if (!$attribute) {
                $output[] = Array(
                    'value' => $node->nodeValue,
                    'node' => $node,
                    'dom' => &$this->_dom   
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
        
