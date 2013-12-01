<?php

namespace yCrawler\Crawler\Runner;

use yCrawler\Crawler\Runner;
use yCrawler\Document;
use Exception;

class BasicRunner extends Runner
{
    private $document;

    public function addDocument(Document $document)
    {
        $this->document = $document;
    }

    public function isFull()
    {
        if ($this->document) return true;
        return false;
    }

    public function wait()
    {
        try {
            $this->document->parse();
            $this->onDone($this->document);
        } catch (Exception $exception) {
            $this->onFailed($this->document, $exception);
        }

        $this->freeDocument();
    }

    protected function freeDocument()
    {
        $this->document = null;
    }
}
