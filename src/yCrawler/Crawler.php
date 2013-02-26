<?php
namespace yCrawler;
use yCrawler\Parser;

class Crawler {
    private $queue;
    private $parsers = Array();
    private $parseCallback;

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

        if ( $this->parseCallback ) $parsers->onParse($this->parseCallback);

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

    public function queue($links) {

    }

    public function onParse(\Closure $closure) {
        $this->parseCallback = &$closure;
        return true;
    }


    //TODO: si no hay getStartupURLs...
    public function run() {
        $this->_start = time();
        foreach($this->parsers as $parser) {
            $parser->configure();
            $this->addLinks($parser->getStartupURLs());
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


    public function jobDone(Document $document) {
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

/*
$pool = new ThreadPool('TestThreadReturnFirstArgument', $threads);

$num     = $jobs_num; // Number of tasks
$left    = $jobs_num; // Number of remaining tasks
$started = array();
do {
    while ($left > 0 && $pool->hasWaiting()) {
        $task = array_shift($jobs);
        if (!$threadId = $pool->run($task)) {
            throw new Exception('Pool slots error');
        }
        $started[$threadId] = $task;
        $left--;
    }
    if ($results = $pool->wait($failed)) {
        foreach ($results as $threadId => $result) {
            unset($started[$threadId]);
            $num--;
            echo 'result: ' . $result . PHP_EOL;
        }
    }
    if ($failed) {
        // Error handling here
        // processing is not successful if thread dies
        // when worked or working timeout exceeded
        foreach ($failed as $threadId) {
            $jobs[] = $started[$threadId];
            echo 'error: ' . $started[$threadId] . PHP_EOL;
            unset($started[$threadId]);
            $left++;
        }
    }
} while ($num > 0);
$pool->cleanup();
*/
}