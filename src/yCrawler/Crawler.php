<?php
namespace yCrawler;
use yCrawler\Parser;

class Crawler {
    private $queue;
    private $parsers = Array();

    protected $threads = 1;
    protected $_links = Array();
    protected $_linksHistory = Array();
    protected $_start;

    public function __construct(Queue $queue) {
        $this->queue = $queue;
    }

    public function add(Parser $parser) {
        $tmp = explode('\\', get_class($parser));
        $name = end($tmp);

        if ( $this->has($name) ) {
            throw new \RuntimeException(
                sprintf('A parser of "%s" class already loaded.', $name)
            );
        }

        $this->parsers[$name] = $parser;
        return true;
    }

    public function has($name) {
        return isset($this->parsers[$name]);
    }

    public function get($name) {
        if ( !$this->has($name) ) return false;
        return $this->parsers[$name];
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

    private function queueDocuments(array $links, Parser $parser) {        
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