<?php
namespace yCrawler;
use yCrawler\Document;
use yCrawler\Misc\URL;
use yCrawler\Parser\Item;
use yCrawler\Parser\Group;

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
abstract class Parser {
    private $initialized = false;
    private $startup = Array();
    private $urlPatterns = Array();
    private $parseCallback;
    private $items = Array(
        'follow' => array(), 'links' => array(),
        'verify' => array(), 'values' => array()
    );   

    abstract public function initialize();
    
    public function configure() {
        if ( $this->initialized ) return true;
        $this->initialize();

        return $this->initialized = true;
    }

    public function setStartupURL($url, $clean = false) {
        if ( $clean ) $this->startup = Array();
        if ( !URL::validate($url) ) {
            throw new Exception('Unable to set startup URL, non-valid url. ' . $url);
        }
        
        return $this->startup[] = $url;
    }

    public function setURLPattern($regexp, $clean = false) {
        if ( $clean ) $this->urlPatterns = Array();
        if ( preg_match($regexp, '') === false ) return false;    
        return $this->urlPatterns[] = $regexp;
    }

    public function createLinkFollowItem($expression = false, $sign = true) {
        $item = new Item();
        if ( $expression ) $item->setPattern($expression);
        $this->items['follow'][] = Array($item, $sign);
        return $item;
    }

    public function createVerifyItem($expression = false, $sign = true) {
        $item = new Item();
        if ( $expression ) $item->setPattern($expression);
        $this->items['verify'][] = Array($item, $sign);
        return $item;
    }

    public function createLinksItem($expression = false, $override = false) { 
        $item = new Item();
        if ( $expression ) $item->setPattern($expression);

        if ( $override ) $this->items['links']= Array($item);
        else $this->items['links'][] = $item;
        return $item;
    }

    public function createValueItem($name, $expression = false) {
        $item = new Item();
        if ( $expression ) $item->setPattern($expression);
        $this->items['values'][$name] = $item;

        return $item;
    }

    public function createValueGroup($name) {
        $group = new Group();
        $this->items['values'][$name] = &$group;
        return $group;
    }

    public function onParse(\Closure $closure) {
        $this->parseCallback = &$closure;
        return true;
    }

    public function parsed(Document $document) {
        if ( !$this->parseCallback ) return null;

        $closure = $this->parseCallback;
        return $closure($document);
    }

    public function matchURL($url) {
        if ( count($this->urlPatterns) == 0 ) {
            $tmp = Array();
            foreach($this->startup as $url) {
                $domain = parse_url($url, PHP_URL_HOST);
                $tmp[] = '~^https?://' . str_replace('.', '\.', $domain) . '~';
            }

            $this->urlPatterns = array_unique($tmp);
        }

        foreach($this->urlPatterns as $regexp) {
            if ( preg_match($regexp, $url) === 1 ) return true;
        } 

        return false;
    }

    public function &getFollowItems() { return $this->items['follow']; }
    public function &getLinksItems() { return $this->items['links']; }
    public function &getVerifyItems() { return $this->items['verify']; }
    public function &getValueItems() { return $this->items['values']; }
    
    public function getStartupURLs() { return $this->startup; }
    public function getURLPatterns() { 
        $this->configure();
        return $this->urlPatterns; 
    }
}