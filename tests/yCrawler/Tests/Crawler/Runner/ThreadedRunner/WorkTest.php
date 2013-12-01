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
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $document = new Document('foo', $this->createParserMock());

        $pool = new Pool();

        $works = [];
        foreach (range(0,1000) as $key => $value) {
            $works[] = $work = new Work($document);
            $pool->submitWork($work);        
        }
    
        $pool->shutdownWorkers();

        foreach ($works as $work) {
            $this->assertInstanceOf('Exception', $work->getException());
        }
    }

    public function testSubmitWorkOk()
    {   

        $document = new Document('http://httpbin.org/', $this->createParserMock());

        $pool = new Pool();

        $work = new Work($document);
        $pool->submitWork($work);

        $pool->shutdownWorkers();

        $this->assertNull($work->getException());
    }
}