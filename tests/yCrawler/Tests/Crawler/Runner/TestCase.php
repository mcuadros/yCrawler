<?php

namespace yCrawler\Tests\Crawler\Runner;

use GuzzleHttp\Client;
use yCrawler\Tests\TestCase as BaseTestCase;
use Exception;
use Mockery as m;

abstract class TestCase extends BaseTestCase
{
    const EXAMPLE_URL = 'http://httpbin.org/';

    abstract protected function createRunner(Client $client);

    public function testOnDoneCallback()
    {
        $uses = 0;
        $callback = function () use (&$uses) {
            $uses++;
        };

        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->once()->andReturn(m::self());
        $client->shouldReceive('getBody')->once();

        $runner = $this->createRunner($client);
        $runner->setOnDoneCallback($callback);

        $doc = $this->createDocumentMock();
        $doc->shouldReceive('getURL')->andReturn('http://url');
        $doc->shouldReceive('setMarkup')->once();
        $doc->shouldReceive('parse')->once();

        $runner->addDocument($doc);
        $runner->wait();

        $this->assertEquals(1, $uses);
    }

    public function testOnFailedCallback()
    {
        $uses = 0;
        $callback = function () use (&$uses) {
            $uses++;
        };

        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->times(3)->andThrow(new Exception());

        $runner = $this->createRunner($client);
        $runner->setOnFailedCallback($callback);

        $doc = $this->createDocumentMock();
        $doc->shouldReceive('getURL')->andReturn('http://url');
        $runner->addDocument($doc);
        $runner->wait();

        $this->assertEquals(3, $uses);
    }

    public function testIsFull()
    {
        $document = $this->createDocumentMock();
        $document->shouldReceive('getURL')->andReturn('http://url');
        $runner = $this->createRunner(new Client());

        $this->assertFalse($runner->isFull());

        for ($i=0; $i<$this->getPoolSize(); $i++) {
            $runner->addDocument($document);
        }

        $this->assertTrue($runner->isFull());
    }

    public function testParseDocumentDone()
    {
        $expectedDocument = null;

        $runner = $this->createRunner(new Client());
        $runner->setOnDoneCallback(
            function ($document) use (&$expectedDocument) {

                $expectedDocument = $document;
            }
        );

        $document = $this->createDocumentMock();
        $document
            ->shouldReceive('parse')->withNoArgs()->once()
            ->andReturn(null);
        $document
            ->shouldReceive('setMarkup')->once()
            ->andReturn(null);
        $document
            ->shouldReceive('getURL')->withNoArgs()
            ->andReturn(self::EXAMPLE_URL);

        $runner->addDocument($document);
        $runner->wait();

        $this->assertSame($expectedDocument, $document);
    }

    public function testParseDocumentFailed()
    {
        $expectedDocument = null;
        $expectedException = null;

        $runner = $this->createRunner(new Client());
        $runner->setOnFailedCallback(
            function ($document, $exception) use (&$expectedDocument, &$expectedException) {
                $expectedDocument = $document;
                $expectedException = $exception;
            }
        );

        $exception = new Exception();

        $document = $this->createDocumentMock();
        $document
            ->shouldReceive('parse')->withNoArgs()
            ->andThrow($exception);
        $document
            ->shouldReceive('setMarkup')
            ->andReturn(null);
        $document
            ->shouldReceive('getURL')->withNoArgs()
            ->andReturn(self::EXAMPLE_URL);

        $runner->addDocument($document);
        $runner->wait();

        $this->assertSame($expectedDocument, $document);
        $this->assertSame($expectedException, $exception);
    }

    public function testRetries()
    {

        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->times(3)->andThrow(new Exception());

        $runner = $this->createRunner($client);
        $doc = $this->createDocumentMock();
        $doc->shouldReceive('getURL')->andReturn('http://url');
        $runner->addDocument($doc);
        $runner->wait();
    }
}
