<?php
namespace yCrawler;

class Crawler_Base extends Base {
    protected $_process;
    protected $_links = Array();
    protected $_linksHistory = Array();
    protected $_parsers = Array();
    protected $_start;
  
    public function __construct() {
        $this->_process = new Crawler_Process_PCNTL;
        $this->_process->setJobDoneCallback(Array(&$this, 'jobDone'));      
    }

    public function &registerParser($className, $maxChilds = null) {
        if ( !$maxChilds ) $maxChilds = Config::get('max_threads_by_parser');
        
        if ( !class_exists($className) ) {
            throw new Exception('Unable to register parser, unknow className.');
        }
        
        $rClass = new \ReflectionClass($className);
        if ( !$rClass->isSubclassOf('yCrawler\Parser_Base') ) {
            throw new Exception('The parser class must extend Parser_Base');
        }

        Output::log('Registering new parser: ' . $className , Output::INFO);
        $this->_parsers[$className] = Array('obj' => new $className, 'maxChilds' => $maxChilds);   
        return $this->_parsers[$className];
    }

    public function &getParser($mixed, $key = 'obj') {
        if ( is_object($mixed) ) { $mixed = get_class($mixed); }

        if ( !array_key_exists($mixed, $this->_parsers) ) return false;
        if ( !$key ) return $this->_parsers[$mixed];
        return $this->_parsers[$mixed][$key];
    }

    public function &foundParser($url, $key = 'obj') {
        foreach( $this->_parsers as $name => &$parser ) {
            if ( $parser['obj']->matchURL($url) ) {
                if ( !$key ) return $parser;
                return $parser[$key];
            } 
        }

        $return = false;
        return $return;
    }

    //TODO: si no hay getStartupURLs...
    public function run() {
        $this->_start = time();
        foreach($this->_parsers as &$parser) {
            $parser['obj']->configure();
            $this->addLinks($parser['obj']->getStartupURLs());
        }

        while(1) {
            $waiting = $this->_process->getWaitingJobs();
            $queue = count($this->_links);
            if ( $waiting == 0 && $queue == 0 ) break;   
            else if ( $waiting >= Config::get('max_threads') ) sleep(1);
            else if ( $queue > 0 ) $this->sendJob();
            else { sleep(1); }
        }
    }

    public function sendJob() {
        $url = array_shift($this->_links);

        $tmp = &$this->foundParser($url, false);
        $parser = &$tmp['obj'];
        $limit = $tmp['maxChilds'];

        if ( !$parser->data('get', 'childs') || $parser->data('get', 'childs') < $limit ) {
            $parser->data('add', 'childs');
            $this->_process->sendJob(new Document($url, $parser)); 
        } else {
            $this->_links[] = $url;
        }

        return true;
    }

    public function jobDone($pid, Document $document) {
        $take = true;
        switch( $status = $document->getStatus() ) { 
            case Request::STATUS_DONE:
            case Request::STATUS_CACHED:
                $this->addLinks($document->getLinks());
                Output::log('Document (done) ' .  $document . ' ' . $document->getUrl() , Output::INFO);
                break;
            case Request::STATUS_RETRY:
                if ( $retry = $document->newRetry() ) {
                    Output::log('Document (retry:'.$retry.') ' . $document->getUrl(), Output::DEBUG);
                    $this->_process->sendJob($document);    
                    $take = false;
                } else {
                    Output::log('Document (max. retries) ' . $document->getUrl(), Output::WARNING);
                }
                break;
            case Request::STATUS_FAILED:
                Output::log('Document (failed) ' . $document . ' ' . $document->getUrl(), Output::WARNING);
                break;
            default:
                Output::log('Document (error) ' . $document . ' ' . $document->getUrl() . ' Unexpected request status: ' . $status, Output::ERROR);
        }

        $this->getParser($document->getParser())->data('sum', $document->getData());
        if ( $take ) $this->getParser($document->getParser())->data('take', 'childs');
        return true;
    }

    public function addLinks(array $links) {        
        $links = array_diff($links, $this->_linksHistory);
        $this->_linksHistory = array_merge($this->_linksHistory, $links);
        $this->_links =  array_merge($this->_links, $links);

        return $links;
    }

    public function __toString() {
        $this->data('set', 'links', count($this->_linksHistory));
        $this->data('set', 'elapsed', microtime(true) - $this->_start);
        foreach($this->_parsers as $data) Output::log( get_class($data) . '::' . $data );
        return parent::__toString();
    }

}