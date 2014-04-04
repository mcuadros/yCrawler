<?php

namespace yCrawler\Crawler;

use yCrawler\Document;
use Exception;
use yCrawler\SerializableClosure;

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

    abstract protected function freeDocument(Document $document);

    abstract public function clean();

    public function setOnDoneCallback(\Closure $callback)
    {
        $this->onDoneCallback = new SerializableClosure($callback);
    }

    public function setOnFailedCallback(\Closure $callback)
    {
        $this->onFailedCallback = new SerializableClosure($callback);
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

        if (!$this->onFailedCallback) {
            return;
        }

        $callback = $this->onFailedCallback;
        $callback($document, $exception);
    }

    protected function onDone(Document $document)
    {

        if (!$this->onDoneCallback) {
            return;
        }

        $callback = $this->onDoneCallback;
        $callback($document);
    }
}
