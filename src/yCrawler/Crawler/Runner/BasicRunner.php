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
        $this->retries[$document->getURL()] = 0;
    }

    public function isFull()
    {
        if ($this->document) {
            return true;
        }

        return false;
    }

    protected function onWait()
    {
        do {
            try {
                $this->parseDocument();
                $this->onDone($this->document);
            } catch (Exception $exception) {
                $this->incRetries($this->document);
                $this->onFailed($this->document, $exception);
            }
        } while ($this->getRetries($this->document) > 0 && $this->getRetries($this->document) <= $this->maxRetries);
        $this->freeDocument($this->document);
    }

    protected function parseDocument()
    {
        $url = $this->document->getURL();

        $this->request->setUrl($url);
        $this->request->execute();

        $this->document->setMarkup($this->request->getResponse());
        $this->document->parse();
    }

    protected function freeDocument(Document $document)
    {
        unset($this->retries[$document->getURL()]);
        $this->document = null;
    }
}
