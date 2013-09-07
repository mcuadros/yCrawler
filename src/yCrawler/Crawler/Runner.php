<?php
namespace yCrawler\Crawler;
use yCrawler\Document;
use Exception;

abstract class Runner
{
    private $onFaildCallback;
    private $onDoneCallback;

    abstract public function parseDocument(Document $document);

    public function setOnDoneCallback(Callable $callback)
    {
        $this->onDoneCallback = $callback;
    }

    public function getOnDoneCallback()
    {
        return $this->onDoneCallback;
    }

    public function setOnFaildCallback(Callable $callback)
    {
        $this->onFaildCallback = $callback;
    }

    public function getOnFaildCallback()
    {
        return $this->onFaildCallback;
    }

    public function onFaild(Document $document, Exception $exception)
    {
        $callback = $this->onFaildCallback;
        $callback($document, $exception);
    }

    public function onDone(Document $document)
    {
        $callback = $this->onDoneCallback;
        $callback($document);
    }
}