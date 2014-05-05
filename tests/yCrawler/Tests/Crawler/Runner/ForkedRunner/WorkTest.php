<?php

namespace yCrawler\Tests\Crawler\Runner\ForkedRunner;

use GuzzleHttp\Client;
use yCrawler\Tests\TestCase;
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

        $work = new Work($document, new Client());
        $this->assertTrue($work->isParsed());
    }

    public function testIsFailed()
    {
        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->once()->andReturn(m::self());
        $client->shouldReceive('getBody')->once();

        $document = $this->createDocumentMock();
        $document->shouldReceive('parse')
            ->withNoArgs()
            ->once()
            ->andThrow(new Exception());
        $document->shouldReceive('getURL');
        $document->shouldReceive('setMarkup');

        $work = new Work($document, $client);
        $work->run();

        $this->assertTrue($work->isFailed());
    }

    public function testGetException()
    {
        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->once()->andReturn(m::self());
        $client->shouldReceive('getBody')->once();

        $exception = new Exception();
        $document = $this->createDocumentMock();
        $document->shouldReceive('parse')
            ->withNoArgs()
            ->once()
            ->andThrow($exception);
        $document->shouldReceive('getURL');
        $document->shouldReceive('setMarkup');

        $work = new Work($document, $client);
        $work->run();

        $this->assertSame($exception, $work->getException());
    }

    public function testGetDocument()
    {
        $client = m::mock('GuzzleHttp\Client');
        $document = $this->createDocumentMock();
        $work = new Work($document, $client);

        $this->assertSame($document, $work->getDocument());
    }
}
