<?php

namespace yCrawler\Tests;

use yCrawler\Crawler;
use yCrawler\Mocks\ParserMock;
use yCrawler\Crawler\Runner\BasicRunner;
use yCrawler\Crawler\Queue\SimpleQueue;
use yCrawler\Document;
use \Mockery as m;

class CrawlerTest extends  \PHPUnit_Framework_TestCase
{
    const EXAMPLE_MARKUP = '<html><body><pre><a href="foo">bar</a></pre></body></html>';

    public function testRun()
    {
        $request = m::mock('yCrawler\Crawler\Request');
        $request->shouldReceive('execute');
        $request->shouldReceive('setUrl');
        $request->shouldReceive('getResponse')->andReturn(self::EXAMPLE_MARKUP);

        $parser = new ParserMock();
        $doc = new Document('http://aurl', $parser);
        $runner = new BasicRunner($request);
        $queue = new SimpleQueue();
        $queue->add($doc);

        $crawler = new Crawler($queue, $runner);
        $crawler->run(0);
        $this->assertTrue($doc->isParsed());
    }
}
