<?php
namespace yCrawler;
use yCrawler\Parser\Item\Modifiers\Scalar;
use yCrawler\Misc\URL;
use yCrawler\Document;

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

    public function evaluate() {
        $this->parser->configure();
        if ( !$this->isVerified() ) return false;

        foreach($this->parser->getValueItems() as $name => $item) { 
            foreach($item->evaluate($this) as $data) $this->addValue($name, $data);
        }

        return $this->getValues();
    }

    public function links() {
        $this->parser->configure();
        if ( !$this->isIndexable() ) return false;

        if ( !$this->parser->getLinksItems() ) {
            $this->parser->createLinksItem('//a/@href');
        }

        foreach($this->parser->getLinksItems() as $item) { 
            foreach($item->evaluate($this) as $data) {
                $url = URL::absolutize($data['value'], $this->getUrl());
                if ( $url && $this->parser->matchURL($url) ) $this->addLink($url);
            }
        }

        return $this->getLinks();
    }
    
    public function parse() {
        if ( $this->parsed ) return $this;
        if ( $this->parser->configure() ) {
            $this->links();
            $this->evaluate();

            $this->parsed = true;
            $this->parser->onParse($this);
        }    

        return $this;
    }

    public function evaluateXPath($expression) {
        if ( !$this->makeRequest() ) return false; 

        $output = Array();
        $result = $this->xpath->evaluate($expression);
        
        if ( !$result ) return null;
        if ( $result->length == 0 )  return null;

        foreach ($result as $node) {
            $output[] = Array(
                'value' => $node->nodeValue,
                'node' => &$node,
                'dom' => &$this->dom   
            );
        }

        if ( count($output) == 0 ) return null;
        return $output;
    }

    public function evaluateRegExp($expression) {
        if ( !$this->makeRequest() ) return false;          
        if ( !preg_match_all($expression, $this->getResponse(), $matches) ) return false;

        foreach(end($matches) as $index => $value) {
            $output[] = Array(
                'value' =>  $value,
                'full' =>  $matches[0][$index]
            );
        } 

        if ( count($output) == 0 ) return null;
        return $output;
    }

    public function getParser() { return $this->parser; }
    public function getLinks() { return array_keys($this->links); }
    public function getValues() { return $this->values; }
    public function getValue($name) { 
        if ( !isset($this->values[$name]) ) return false; 
        return $this->values[$name]; 
    }

    public function isVerified() {
        if ( $this->verified === null ) $this->verified = $this->verify();
        return $this->verified;
    }
    
    public function isIndexable() {
        if ( $this->indexable === null ) $this->indexable = $this->follow();
        return $this->indexable;
    }
    
    public function isParsed() { return $this->parsed; }

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

    protected function verify() {
        if ( !$this->makeRequest() ) return false; 
        if ( !$verifyItems = $this->parser->getVerifyItems() ) return true;

        foreach($verifyItems as &$item) {
            $item[0]->setModifier(Scalar::boolean($item[1]));
            if ( !$item[0]->evaluate($this) ) return false;
        }

        return true;
    }

    protected function follow() {
        if ( !$this->makeRequest() ) return false;
        if ( !$followItems = $this->parser->getFollowItems() ) return true;

        foreach($followItems as &$item) {
            $item[0]->setModifier(Scalar::boolean($item[1]));
            if ( !$item[0]->evaluate($this) ) return false;
        }

        return true;
    }

    protected function addValue($name, $data, $override = false) {
        if ( $override ) $this->values[$name] = Array();
        if ( is_array($data) ) return $this->values[$name][] = $data['value'];
        return $this->values[$name][] = $data;
    }
    
    protected function addLink($url, $override = false) {
        if( $override ) $this->links = array($url => true);
        else $this->links[$url] = true;
        return $url;
    }    
}