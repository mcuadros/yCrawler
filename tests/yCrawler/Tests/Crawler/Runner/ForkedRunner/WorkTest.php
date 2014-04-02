<?php

namespace yCrawler\Tests\Crawler\Runner\ForkedRunner;

use yCrawler\Tests\TestCase;
use yCrawler\Crawler\Request;
use yCrawler\Crawler\Runner\ForkedRunner\Work;
use yCrawler\Document;
use Exception;
use Mockery as m;

class WorkTest extends TestCase
{
    public function testIsParsed()
    {
        $document = $this->createDocumentMock();
        $document->shouldReceive('isParsed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $work = new Work($document, new Request());
        $this->assertTrue($work->isParsed());
    }

    public function testIsFailed()
    {
        $request = m::mock('yCrawler\Crawler\Request');
        $request->shouldReceive('setUrl');
        $request->shouldReceive('execute');
        $request->shouldReceive('getResponse');

        $document = $this->createDocumentMock();
        $document->shouldReceive('parse')
            ->withNoArgs()
            ->once()
            ->andThrow(new Exception());
        $document->shouldReceive('getURL');
        $document->shouldReceive('setMarkup');

        $work = new Work($document, $request);
        $work->run();

        $this->assertTrue($work->isFailed());
    }

    public function testGetException()
    {
        $request = m::mock('yCrawler\Crawler\Request');
        $request->shouldReceive('setUrl');
        $request->shouldReceive('execute');
        $request->shouldReceive('getResponse');

        $exception = new Exception();
        $document = $this->createDocumentMock();
        $document->shouldReceive('parse')
            ->withNoArgs()
            ->once()
            ->andThrow($exception);
        $document->shouldReceive('getURL');
        $document->shouldReceive('setMarkup');

        $work = new Work($document, $request);
        $work->run();

        $this->assertSame($exception, $work->getException());
    }

    public function testGetDocument()
    {
        $request = m::mock('yCrawler\Crawler\Request');
        $document = $this->createDocumentMock();
        $work = new Work($document, $request);

        $this->assertSame($document, $work->getDocument());
    }
}
