<?php

namespace yCrawler\Crawler\Runner\ForkedRunner;

use GuzzleHttp\Client;
use yCrawler\Document;
use Exception;

class Work
{
    private $document;
    private $client;
    private $exception;
    private $isFailed;

    public function __construct(Document $document, Client $client)
    {
        $this->document = $document;
        $this->client = $client;
    }

    public function run()
    {
        $this->parseDocument();
    }

    private function parseDocument()
    {
        $url = $this->document->getURL();

        try {
            $this->document->setMarkup($this->client->get($url)->getBody());
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
