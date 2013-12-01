<?php

namespace yCrawler\Tests\Crawler\Runner\ForkedRunner;

use yCrawler\Tests\TestCase;
use yCrawler\Crawler\Runner\ForkedRunner\Work;
use yCrawler\Crawler\Runner\ForkedRunner\Pool;
use yCrawler\Document;
use Exception;

class WorkTest extends TestCase
{
    public function testIsParsed()
    {   
        $document = $this->createDocumentMock();
        $document->shouldReceive('isParsed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $work = new Work($document);
        $this->assertTrue($work->isParsed());
    }

    public function testIsFailed()
    {   
        $document = $this->createDocumentMock();
        $document->shouldReceive('parse')
            ->withNoArgs()
            ->once()
            ->andThrow(new Exception());

        $work = new Work($document);
        $work->run();

        $this->assertTrue($work->isFailed());
    }

    public function testGetException()
    {   
        $exception = new Exception();
        $document = $this->createDocumentMock();
        $document->shouldReceive('parse')
            ->withNoArgs()
            ->once()
            ->andThrow($exception);

        $work = new Work($document);
        $work->run();

        $this->assertSame($exception, $work->getException());
    }

    public function testGetDocument()
    {   
        $document = $this->createDocumentMock();
        $work = new Work($document);
      
        $this->assertSame($document, $work->getDocument());
    }
}