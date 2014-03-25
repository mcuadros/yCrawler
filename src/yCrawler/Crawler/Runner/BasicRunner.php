<?php

namespace yCrawler\Crawler\Runner;

use yCrawler\Crawler\Request;
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
        if ($this->document) {
            return true;
        }

        return false;
    }

    public function wait()
    {
        try {
            $this->parseDocument();
            $this->onDone($this->document);
        } catch (Exception $exception) {
            $this->onFailed($this->document, $exception);
        }

        $this->freeDocument();
    }

    protected function parseDocument()
    {
        $url = $this->document->getUrl();

        $request = new Request($url);
        $request->execute();

        $this->document->setMarkup($request->getResponse());
        $this->document->parse();
    }

    protected function freeDocument()
    {
        $this->document = null;
    }
}
