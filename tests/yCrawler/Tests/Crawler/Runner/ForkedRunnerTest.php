<?php

namespace yCrawler\Tests\Crawler\Runner;

use yCrawler\Crawler\Queue\SimpleQueue;
use yCrawler\Crawler\Request;
use yCrawler\Crawler\Runner\ForkedRunner;
use Mockery as m;
use yCrawler\Crawler;
use yCrawler\Document;
use yCrawler\Mocks\DocumentMock;
use yCrawler\Mocks\ParserMock;
use yCrawler\Parser;

class ForkedRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsFull()
    {
        $this->markTestIncomplete('this is not testing anything');
        $request = m::mock('yCrawler\Crawler\Request');
        $request->shouldReceive('setUrl');
        $request->shouldReceive('execute');
        $request->shouldReceive('getResponse');

        $document = m::mock('yCrawler\Document');
        $document->shouldReceive('getURL');
        $document->shouldReceive('parse');
        $document->shouldReceive('setMarkup');
        $document->shouldReceive('isParsed')->andReturn(true);

        $runner = new ForkedRunner($request);
        $runner->addDocument($document);

        $this->assertTrue($runner->isFull());
    }

    public function testIsNotFull()
    {
        $request = m::mock('yCrawler\Crawler\Request');

        $runner = new ForkedRunner($request);
        $this->assertFalse($runner->isFull());
    }

    public function testRunnerRunsSuccessfully()
    {
        $this->markTestIncomplete('this is not testing anything');
        $request = m::mock('yCrawler\Crawler\Request');
        $request->shouldReceive('execute');
        $request->shouldReceive('setUrl');
        $request->shouldReceive('getResponse');

        $document = new DocumentMock('yunait.com', new Parser('test'));
        $document->isParsed = true;
        $document2 = new DocumentMock('yunait.es', new Parser('test'));
        $document2->isParsed = true;

        $runner = new ForkedRunner($request);

        $runner->addDocument($document);
        $runner->addDocument($document);
        $runner->wait();
    }
}
