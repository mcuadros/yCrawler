<?php

namespace yCrawler;

use yCrawler\Parser;
use yCrawler\Crawler\Runner;
use yCrawler\Crawler\Queue;
use yCrawler\Crawler\Exceptions;

class Crawler
{
    const LOOP_WAIT_TIME = 4;

    protected $initialized;
    protected $runner;
    protected $queue;
    protected $parseCallback;
    protected $parsers = Array();

    public function __construct(Queue $queue, Runner $runner)
    {
        $this->runner = $runner;
        $this->queue = $queue;
    }

    public function initialize()
    {
        if ($this->initialized) {
            return true;
        }

        foreach ($this->parsers as $parser) {
            $this->queueDocuments($parser->getStartupDocuments());
        }

        return $this->initialized = time();
    }

    public function addDocument(Document $document)
    {
        $this->queue->add($document);
    }

    protected function queueDocuments(Array $documents)
    {
        foreach ($documents as $document) {
            $this->addDocument($document);
        }
    }

    public function addParser(Parser $parser)
    {
        $name = $parser->getName();
        if ($this->hasParser($name)) {
            throw new Exceptions\ParserAlreadyLoaded($parser);
        }

        if ($this->parseCallback) {
            $parser->onParse($this->parseCallback);
        }

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

    public function onParse(Callable $callable)
    {
        $this->parseCallback = $callable;
        $this->setOnParserCallbackOnParsers();
    }

    protected function setOnParserCallbackOnParsers()
    {
        foreach ($this->parsers as $parser) {
            $parser->setOnParseCallback($this->parseCallback);
        }
    }

    public function run()
    {
        $this->initialize();

        while ($this->queue->count() > 0) {
            $this->addDocumentsToRunnerWhileNotIsFull();
            $this->runner->wait();
            sleep(self::LOOP_WAIT_TIME);
        }
    }

    protected function addDocumentsToRunnerWhileNotIsFull()
    {
        while (!$this->runner->isFull()) {
            echo "!isFull" . PHP_EOL;
            $document = $this->queue->get();
            $this->runner->addDocument($document);
        }
    }

    public function jobDone(Document $document)
    {
        var_dump($document);
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
}
