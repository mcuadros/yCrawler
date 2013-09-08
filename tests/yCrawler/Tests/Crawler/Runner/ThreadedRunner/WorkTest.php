<?php
namespace yCrawler\Tests;
use yCrawler\Tests\TestCase;
use yCrawler\Crawler\Runner\ThreadedRunner\Work;
use yCrawler\Crawler\Runner\ThreadedRunner\Pool;
use yCrawler\Document;
use Exception;

class WorkTest extends TestCase
{
    public function testSubmitWorkFail()
    {   
        $document = new Document('foo', $this->createParserMock());

        $pool = new Pool();
        
        $work = new Work($document);
        $pool->submitWork($work);

        $pool->shutdownWorkers();

        $this->assertInstanceOf('Exception', $work->getException());
    }

    public function testSubmitWorkOk()
    {   

        $document = new Document('http://httpbin.org/', $this->createParserMock());

        $pool = new Pool();

        $work = new Work($document);
        $pool->submitWork($work);

        $work = new Work($document);
        $pool->submitWork($work);

        $pool->shutdownWorkers();

        $this->assertNull($work->getException());
    }
}