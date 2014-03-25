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
    protected $parsers = [];

    public function __construct(Queue $queue, Runner $runner)
    {
        $this->runner = $runner;
        $this->queue = $queue;
    }

    public function addDocument(Document $document)
    {
        $this->queue->add($document);
    }

    public function overrideOnParse(Callable $callable)
    {
        $this->parseCallback = $callable;
    }

    public function run($loopWaitTime = self::LOOP_WAIT_TIME)
    {
        $this->initialize();

        while ($this->queue->count() > 0) {
            $this->addDocumentsToRunner();
            $this->runner->wait();
            sleep($loopWaitTime);
        }
    }

    protected function addDocumentsToRunner()
    {
        while (!$this->runner->isFull()) {
            $document = $this->queue->get();
            if ($this->parseCallback) {
                $document->getParser()->setOnParseCallback($this->parseCallback);
            }
            $this->runner->addDocument($document);
        }
    }

    private function initialize()
    {
        if ($this->initialized) {
            return true;
        }

        $this->runner->setOnDoneCallback(
            function ($document) {
                if (!$document->isIndexable()) {
                    return;
                }
                foreach($document->getLinks() as $url => $pass) {
                    $this->queue->add(new Document($url, $document->getParser()));
                }

            }
        );

        return $this->initialized = time();
    }

}
