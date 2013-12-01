<?php

namespace yCrawler\Crawler\Runner\ThreadedRunner;

use yCrawler\Document;
use Stackable;
use Exception;

class Work extends Stackable
{
    private $originalDocument;
    private $document;
    private $exception;

    private $failed;
    private $read;

    public function __construct(Document $document)
    {
        $this->originalDocument = $document;
    }

    public function run()
    {
        $this->parseDocument();
    }

    private function parseDocument()
    {
        $document = $this->copyDocument();

        try {
            $document->parse();
        } catch (Exception $exception) {
            $this->exception = $exception;
            $this->failed = true;
        }
    }

    private function copyDocument()
    {
        $this->document = clone $this->originalDocument;

        return $this->document;
    }

    public function isParsed()
    {
        if (!$this->document) return false;
        return $this->document->isParsed();
    }

    public function isFailed()
    {
        return $this->isFailed;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function dataWaiting()
    {
        if ($this->isParsed()) {
            $this->read = true;
        }

        return !$this->read;
    }
}
