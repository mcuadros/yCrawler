<?php

namespace yCrawler\Crawler;

use yCrawler\Document;
use Exception;

abstract class Runner
{
    protected $request;
    protected $retries = [];
    protected $maxRetries = 2;
    private $onFailedCallback;
    private $onDoneCallback;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    abstract public function isFull();

    abstract public function addDocument(Document $document);

    abstract protected function onWait();

    abstract protected function freeDocument();

    public function setOnDoneCallback(callable $callback)
    {
        $this->onDoneCallback = $callback;
    }

    public function setOnFailedCallback(callable $callback)
    {
        $this->onFailedCallback = $callback;
    }

    public function wait()
    {
        $this->onWait();
    }

    public function getRetries(Document $document)
    {
        return $this->retries[$document->getURL()];
    }

    public function incRetries(Document $document)
    {
        $this->retries[$document->getURL()]++;
    }

    protected function onFailed(Document $document, Exception $exception)
    {
        $this->freeDocument();
        if (!$this->onFailedCallback) {
            return;
        }

        $callback = $this->onFailedCallback;
        $callback($document, $exception, $this->retries);
    }

    protected function onDone(Document $document)
    {
        $this->freeDocument();
        if (!$this->onDoneCallback) {
            return;
        }

        $callback = $this->onDoneCallback;
        $callback($document);
    }
}
