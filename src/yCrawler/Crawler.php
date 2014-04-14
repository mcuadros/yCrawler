<?php

namespace yCrawler;

use yCrawler\Crawler\Web;
use yCrawler\Parser;
use yCrawler\Crawler\Runner;
use yCrawler\Crawler\Queue;
use yCrawler\Crawler\Exceptions;
use yCrawler\Document;

class Crawler
{
    const LOOP_WAIT_TIME = 2;

    protected $initialized;
    protected $runner;
    protected $queue;
    protected $parseCallback;
    protected $parsers = [];
    protected $works;
    protected $webs;

    public function __construct(array $webs)
    {
        $this->webs = $webs;
    }

    public function addDocument(Document $document)
    {
        $this->queue->add($document);
    }

    public function overrideOnParse(callable $callable)
    {
        $this->parseCallback = $callable;
    }

    public function run($loopWaitTime = self::LOOP_WAIT_TIME)
    {
        $webs = $this->webs;
        do {
            foreach ($webs as $k => $web) {
                if (!$web instanceof Web) {
                    throw new \InvalidArgumentException('Not an instance of Web');
                }

                if ($web->queue->count() <= 0) {
                    unset($webs[$k]);
                    continue;
                }

                $this->initializeRunner($web);
                $this->addDocumentsToRunner($web);
                if (!$this->runnerShouldWait($web)) {
                    $web->runner->wait();
                }
            }
            sleep($loopWaitTime);
        } while (count($webs) > 0);
    }

    protected function addDocumentsToRunner($web)
    {
        $hash = spl_object_hash($web->runner);
        while (!$web->runner->isFull() && !$this->runnerShouldWait($web) && $document = $web->queue->get()) {
            if ($this->parseCallback) {
                $document->getParser()->setOnParseCallback($this->parseCallback);
            }
            $web->runner->addDocument($document);
            $this->works[$hash]['waitTime'] = time() + $web->waitTime;
        }
    }

    protected function runnerShouldWait($web)
    {
        $hash = spl_object_hash($web->runner);

        if ($this->works[$hash]['waitTime'] <= time() &&
            $web->parallelRequests >= $this->works[$hash]['currentRequests']
        ) {
            return false;
        }

        return true;
    }

    private function initializeRunner($web)
    {
        $hash = spl_object_hash($web->runner);

        if (isset($this->works[$hash])) {
            return null;
        }

        $this->works[$hash]['waitTime'] = 0;
        $this->works[$hash]['currentRequests'] = 0;

        $web->runner->setOnDoneCallback($this->getOnDoneCallback($web->queue, $web->runner, $this->works));
        $web->runner->setOnFailedCallback($this->getOnFailedCallback($web->runner, $this->works));

        return $this->initialized = time();
    }

    private function getOnFailedCallback($runner, $works)
    {
        return function ($document, $exception) use ($runner, $works) {
            $works[spl_object_hash($runner)]['currentRequests']--;
            if ($runner->getRetries($document) < 3) {
                $runner->incRetries($document);
            }
        };
    }

    private function getOnDoneCallback($queue, $runner, $works)
    {
        return function ($document) use ($queue, $runner, $works) {
            $works[spl_object_hash($runner)]['currentRequests']--;
            if (!$document->isIndexable()) {
                return;
            }
            foreach ($document->getLinks() as $url => $pass) {
                $queue->add(new Document($url, $document->getParser()));
            }
        };
    }
}
