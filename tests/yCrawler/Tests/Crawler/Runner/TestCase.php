<?php

namespace yCrawler\Tests\Crawler\Runner;

use yCrawler\Crawler\Request;
use yCrawler\Tests\TestCase as BaseTestCase;
use Exception;
use Mockery as m;

abstract class TestCase extends BaseTestCase
{
    const EXAMPLE_URL = 'http://httpbin.org/';

    abstract protected function createRunner(Request $request);

    public function testOnDoneCallback()
    {
        $uses = 0;
        $callback = function () use (&$uses) {
            $uses++;
        };

        $request = m::mock('yCrawler\Crawler\Request');
        $request->shouldReceive('setUrl');
        $request->shouldReceive('execute')->times(1);
        $request->shouldReceive('getResponse')->times(1);

        $runner = $this->createRunner($request);
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

        $request = m::mock('yCrawler\Crawler\Request');
        $request->shouldReceive('setUrl');
        $request->shouldReceive('execute')->times(3)->andThrow(new Request\Exceptions\NetworkError());

        $runner = $this->createRunner($request);
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
        $runner = $this->createRunner(new Request());

        $this->assertFalse($runner->isFull());

        for ($i=0; $i<$this->getPoolSize(); $i++) {
            $runner->addDocument($document);
        }

        $this->assertTrue($runner->isFull());
    }

    public function testParseDocumentDone()
    {
        $expectedDocument = null;

        $runner = $this->createRunner(new Request());
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

        $runner = $this->createRunner(new Request());
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

        $request = m::mock('yCrawler\Crawler\Request');
        $request->shouldReceive('setUrl');
        $request->shouldReceive('execute')->times(3)->andThrow(new Request\Exceptions\NetworkError());

        $runner = $this->createRunner($request);
        $doc = $this->createDocumentMock();
        $doc->shouldReceive('getURL')->andReturn('http://url');
        $runner->addDocument($doc);
        $runner->wait();
    }
}
