<?php

namespace yCrawler\Crawler\Runner;

use yCrawler\Crawler\Request;
use yCrawler\Crawler\Runner;
use yCrawler\Crawler\Runner\ForkedRunner\Pool;
use yCrawler\Crawler\Runner\ForkedRunner\Work;
use yCrawler\Crawler\Runner\ForkedRunner\Exceptions;
use yCrawler\Document;

class ForkedRunner extends Runner
{
    private $pool;
    private $running;

    public function __construct(Request $request)
    {
        $this->pool = new Pool();
        parent::__construct($request);
    }

    public function addDocument(Document $document)
    {
        $closure = new SerializableClosure($document->getOnParseCallback());
        $document->getParser()->setOnParseCallback($closure);

        $this->retries[$document->getURL()] = 0;
        $work = new Work($document, $this->request);
        $this->runWorkInPool($work);
    }

    public function isFull()
    {
        return !$this->pool->hasWaiting();
    }

    protected function onWait()
    {
        if ($results = $this->pool->wait($failed)) {
            foreach ($results as $threadId => $work) {
                $this->onWorkFinished($threadId, $work);
            }
        }

        if ($failed) {
            foreach ($failed as $threadId => $error) {
                $this->onWorkFailed($threadId, $error);
            }
        }
    }

    protected function freeDocument(Document $document)
    {
        unset($this->retries[$document->getURL()]);
    }

    private function onWorkFailed($threadId, Array $error)
    {
        $work = $this->popRunningWorkByThreadId($threadId);
        $exception = new Exceptions\NonParsedDocument($error[0], $error[1]);

        $this->onFailed($work->getDocument(), $exception);
    }

    private function onWorkFinished($threadId, Work $work)
    {
        $this->popRunningWorkByThreadId($threadId);

        if ($work->isParsed() && !$work->isFailed()) {
            $this->onDone($work->getDocument());
            return;
        }

        if (!$work->isParsed() && !$work->isFailed()) {
            $exception = $this->createNonParsedDocument();
        } elseif ($work->isFailed()) {
            $exception = $work->getException();
        }

        $this->onFailed($work->getDocument(), $exception);
    }

    private function createNonParsedDocument()
    {
        return new Exceptions\NonParsedDocument();
    }

    private function popRunningWorkByThreadId($threadId)
    {
        if (isset($this->running[$threadId])) {
            $work = $this->running[$threadId];
            unset($this->running[$threadId]);
            return $work;
        }

        return null;
    }

    private function runWorkInPool(Work $work)
    {
        if (!$this->pool->hasWaiting()) {
            //throw new \RuntimeException('no free threads');
            return;
        }
        $threadId = $this->pool->run($work);
        $this->running[$threadId] = $work;
    }
}
