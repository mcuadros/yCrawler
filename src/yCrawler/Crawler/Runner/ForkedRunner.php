<?php

namespace yCrawler\Crawler\Runner;

use yCrawler\Crawler\Runner;
use yCrawler\Crawler\Runner\ForkedRunner\Pool;
use yCrawler\Crawler\Runner\ForkedRunner\Work;
use yCrawler\Crawler\Runner\ForkedRunner\Exceptions;
use yCrawler\Document;
use Exception;

class ForkedRunner extends Runner
{
    private $pool;
    private $works;
    private $running;

    public function __construct()
    {
        $this->pool = new Pool();
    }

    public function addDocument(Document $document)
    {
        $work = $this->createWork($document);
        $this->runWorkInPool($work);
    }

    private function createWork(Document $document)
    {
        return new Work($document);
    }

    private function runWorkInPool(Work $work)
    {
        if (!$threadId = $this->pool->run($work)) {
            throw new Exception('Pool slots error');
        }

        $this->running[$threadId] = $work;
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

    protected function freeDocument()
    {
        unset($this->retries[$this->document->getURL()]);
        $this->document = null;
    }

    private function onWorkFailed($threadId, Array $error)
    {
        $work = $this->popRunnigWorkByThreadId($threadId);
        $exception = new Exceptions\NonParsedDocument($error[0], $error[1]);

        $this->onFailed($work->getDocument(), $exception);
    }

    private function onWorkFinished($threadId, Work $work)
    {
        $this->popRunnigWorkByThreadId($threadId);

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

    private function popRunnigWorkByThreadId($threadId)
    {
        if (isset($this->running[$threadId])) {
            $work = $this->running[$threadId];
            unset($this->running[$threadId]);

            return $work;
        }

        return null;
    }
}

/*

do {
    while ($left > 0 && $pool->hasWaiting()) {
        if (!$threadId = $pool->run($left)) {
            throw new Exception('Pool slots error');
        }
        $left--;
    }
    if ($results = $pool->wait($failed)) {
        foreach ($results as $threadId => $result) {
            $num--;
            echo "result: $result (thread $threadId)", PHP_EOL;
        }
    }
    if ($failed) {
        // Error handling here
        // processing is not successful if thread dies
        // when worked or working timeout exceeded
        foreach ($failed as $threadId => $err) {
            list($errorCode, $errorMessage) = $err;
            echo "error (thread $threadId): #$errorCode - $errorMessage", PHP_EOL;
            $left++;
        }
    }
} while ($num > 0);

*/
/*
        $loops = 0;
        while (1) {
            try {
                if ($loops++ > 5) break;

                if ($results = $pool->wait($failed)) {
                    foreach ($results as $threadId => $result) {
                        $resultDocument = $result;
                        //echo "result: $result (thread $threadId)", PHP_EOL;
                    }
                }

                if ($failed) {
                    var_dump($failed);
                }

                echo "Loop";
            } catch (Exception $e) {
                echo $e->getMessage();
                break;
            }
        }
        */
