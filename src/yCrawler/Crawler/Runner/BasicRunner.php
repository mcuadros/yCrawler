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

    public function wait()
    {
        do {
            try {
                $this->parseDocument();
                $this->onDone($this->document);
            } catch (Exception $exception) {
                $this->incRetries($this->document);
                $this->onFailed($this->document, $exception);
            }
        } while ($this->getRetries($this->document) > 0 && $this->getRetries($this->document) < 3);
        $this->freeDocument();
    }

    protected function parseDocument()
    {
        $url = $this->document->getUrl();

        $this->request->setUrl($url);
        $this->request->execute();

        $this->document->setMarkup($this->request->getResponse());
        $this->document->parse();
    }

    protected function freeDocument()
    {
        unset($this->retries[$this->document->getURL()]);
        $this->document = null;
    }
}
