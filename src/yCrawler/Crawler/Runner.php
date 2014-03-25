<?php

namespace yCrawler\Crawler;

use yCrawler\Document;
use Exception;

abstract class Runner
{
    private $onFailedCallback;
    private $onDoneCallback;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    abstract public function isFull();

    abstract public function wait();

    abstract public function addDocument(Document $document);

    public function setOnDoneCallback(Callable $callback)
    {
        $this->onDoneCallback = $callback;
    }

    public function getOnDoneCallback()
    {
        return $this->onDoneCallback;
    }

    public function setOnFailedCallback(Callable $callback)
    {
        $this->onFailedCallback = $callback;
    }

    public function getOnFailedCallback()
    {
        return $this->onFailedCallback;
    }

    public function onFailed(Document $document, Exception $exception)
    {
        if (!$this->onFailedCallback) {
            echo $exception->getMessage();
            echo $exception->getTraceAsString();

            return;
        }

        $callback = $this->onFailedCallback;
        $callback($document, $exception);
    }

    public function onDone(Document $document)
    {
        if (!$this->onDoneCallback) {
            return;
        }

        $callback = $this->onDoneCallback;
        $callback($document);
    }
}
