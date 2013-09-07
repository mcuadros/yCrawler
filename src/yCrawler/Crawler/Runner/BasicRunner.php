<?php
namespace yCrawler\Crawler\Runner;
use yCrawler\Crawler\Runner;
use yCrawler\Document;
use Exception;

class BasicRunner extends Runner
{
    public function parseDocument(Document $document)
    {
        try {
            $document->parse();
            $this->onDone($document);
        } catch (Exception $exception) {
            $this->onFaild($document, $exception);
        }
    }
}