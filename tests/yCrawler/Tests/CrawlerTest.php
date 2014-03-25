<?php

namespace yCrawler\Tests;

use yCrawler\Crawler;
use yCrawler\Parser;
use yCrawler\Queue;
use yCrawler\Crawler\Runner\BasicRunner;
use yCrawler\Crawler\Queue\SimpleQueue;
use yCrawler\Document;
use yCrawler\Crawler\Thread;
use yCrawler\Crawler\ThreadPool;
use \Mockery as m;

class CrawlerTest extends  \PHPUnit_Framework_TestCase
{
    public function createCrawler()
    {
        $this->markTestSkipped('WIP.');

        $this->queue = new Queue();
        $this->pool = new ThreadPool('yCrawler\Crawler\Thread', 5);

        return new Crawler($this->queue, $this->pool);
    }

    public function testAdd()
    {
        $this->markTestSkipped('WIP.');
        $crawler = $this->createCrawler();
        $this->assertTrue($crawler->addParser(new CrawlerTest_ParserMock));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testAddTwice()
    {
        $this->markTestSkipped('WIP.');
        $crawler = $this->createCrawler();
        $crawler->addParser(new CrawlerTest_ParserMock);
        $crawler->addParser(new CrawlerTest_ParserMock);

    }

    public function testHas()
    {
        $this->markTestSkipped('WIP.');
        $crawler = $this->createCrawler();
        $crawler->addParser(new CrawlerTest_ParserMock);

        $this->assertTrue($crawler->hasParser('CrawlerTest_ParserMock'));
    }

    public function testGet()
    {
        $this->markTestSkipped('WIP.');
        $crawler = $this->createCrawler();
        $crawler->addParser(new CrawlerTest_ParserMock);

        $this->assertInstanceOf(
            'yCrawler\Parser',
            $crawler->getParser('CrawlerTest_ParserMock')
        );
    }

    public function testOnParse()
    {
        $this->markTestSkipped('WIP.');
        $parser = new CrawlerTest_ParserMock;

        $crawler = $this->createCrawler();
        $crawler->onParse(function($document) {
            return get_class($document);
        });

        $crawler->addParser($parser);

        $result = $parser->parsed(new Document('http://test.com'));
        $this->assertSame('yCrawler\Document', $result);
    }

    public function testInitialize()
    {
        $crawler = $this->createCrawler();
        $crawler->addParser(new CrawlerTest_ParserMock);

        $crawler->initialize();

        $doc = $this->queue->get();
        $this->assertSame('http://httpbin.org/', $doc->getUrl());
    }

    public function testRun()
    {
        $this->fail('complete after the refactor');
    }
}
