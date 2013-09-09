<?php
namespace yCrawler\Crawler\Runner;
use yCrawler\Crawler\Runner;
use yCrawler\Crawler\Runner\ThreadedRunner\Pool;
use yCrawler\Crawler\Runner\ThreadedRunner\Work;
use yCrawler\Document;
use Exception;

class ForkedRunner extends Runner
{
    private $pool;
    private $works;

    public function __construct()
    {
        $this->pool = new Pool();
    }

    public function parseDocument(Document $document)
    {
        $work = $this->createWork($document);
        $this->pool->submitWork($work);

    }

    private function createWork(Document $document)
    {
        $this->works[] = $work = new Work($document);
        return $work;
    }

}