<?php
namespace yCrawler;
use yCrawler\Parser;
use yCrawler\Crawler;

class Crawler
{
    private $initialized = false;
    private $history;
    private $queue;

    private $parsers = Array();
    private $parseCallback;

    protected $threads = 1;
    protected $_links = Array();
    protected $_linksHistory = Array();
    protected $_start;

    public function __construct(Queue $queue, ThreadPool $pool)
    {
        $pool->setQueue($queue);

        $this->pool = $pool;
        $this->queue = $queue;
    }

    public function setQueue(Crawler\Queue $queue)
    {
        $this->queue = $queue;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function setHistory(Crawler\History $history)
    {
        $this->history = $history;
    }

    public function getHistory()
    {
        return $this->history;
    }














/*****************/
    public function initialize()
    {
        if ($this->initialized) return true;

        foreach ($this->parsers as $parser) {
            $this->queueDocs($parser->getStartupDocs());
        }

        return $this->initialized = time();
    }

    public function addParser(Parser $parser)
    {
        $tmp = explode('\\', get_class($parser));
        $name = end($tmp);

        if ($this->hasParser($name)) {
            throw new \RuntimeException(
                sprintf('A parser of "%s" class already loaded.', $name)
            );
        }

        if ($this->parseCallback) $parser->onParse($this->parseCallback);

        $this->parsers[$name] = $parser;

        return true;
    }

    public function hasParser($name)
    {
        return isset($this->parsers[$name]);
    }

    public function getParser($name)
    {
        if (!$this->hasParser($name)) return false;
        return $this->parsers[$name];
    }

    public function onParse(\Closure $closure)
    {
        if ($this->parseCallback) {
            foreach( $this->parsers as $parser ) $parser->onParse($this->parseCallback);
        }

        $this->parseCallback = $closure;

        return true;
    }

    private function queueDocs(array $documents)
    {
        foreach ($documents as $document) $this->queue->add($document);
    }

    //TODO: si no hay getStartupURLs...
    public function run()
    {
        $this->initialize();
        $this->pool->start();
    }

    public function jobDone(Document $document)
    {
        $take = true;
        switch ( $status = $document->getStatus() ) {
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
