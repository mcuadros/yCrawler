<?php
namespace yCrawler\Tests\Crawler\Runner;
use yCrawler\Tests\TestCase as BaseTestCase;
use Exception;

class TestCase extends BaseTestCase
{
    public function testSetAndGetOnDoneCallback()
    {
        $callback = function() {};

        $runner = $this->createRunner();
        $runner->setOnDoneCallback($callback);

        $this->assertSame($callback, $runner->getOnDoneCallback());
    }

    public function testSetAndGetOnFailedCallback()
    {
        $callback = function() {};

        $runner = $this->createRunner();
        $runner->setOnFailedCallback($callback);

        $this->assertSame($callback, $runner->getOnFailedCallback());
    }

    public function testIsFull()
    {
        $document = $this->createDocumentMock();
        $runner = $this->createRunner();

        $this->assertFalse($runner->isFull());

        for($i=0;$i<$this->getPoolSize();$i++) {
            $runner->addDocument($document);
        }

        $this->assertTrue($runner->isFull());
    }

    public function testParseDocumentDone()
    {
        $expectedDocument = null;

        $runner = $this->createRunner();
        $runner->setOnDoneCallback(
            function($document) 
            use (&$expectedDocument) {
                $expectedDocument = $document;
        });

        $document = $this->createDocumentMock();
        $document->shouldReceive('parse')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $runner->addDocument($document);
        $runner->wait();

        $this->assertSame($expectedDocument, $document);
    }

    public function testParseDocumentFailed()
    {
        $expectedDocument = null;
        $expectedException = null;

        $runner = $this->createRunner();
        $runner->setOnFailedCallback(
            function($document, $exception) 
            use (&$expectedDocument, &$expectedException) {
                $expectedDocument = $document;
                $expectedException = $exception;
        });

        $exception = new Exception();

        $document = $this->createDocumentMock();
        $document->shouldReceive('parse')
            ->withNoArgs()
            ->once()
            ->andThrow($exception);


        $runner->addDocument($document);
        $runner->wait();

        $this->assertSame($expectedDocument, $document);
        $this->assertSame($expectedException, $exception);
    }
}

