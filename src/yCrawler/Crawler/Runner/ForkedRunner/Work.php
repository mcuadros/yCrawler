<?php

namespace yCrawler\Crawler\Runner\ForkedRunner;

use yCrawler\Document;
use Exception;

class Work
{
    private $document;
    private $exception;
    private $isFailed;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function run()
    {
        $this->parseDocument();
    }

    private function parseDocument()
    {
        try {
            $this->document->parse();
        } catch (Exception $exception) {
            $this->exception = $exception;
            $this->isFailed = true;
        }
    }

    public function isParsed()
    {
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
}
