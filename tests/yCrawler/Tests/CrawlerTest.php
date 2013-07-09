<?php
namespace yCrawler\Tests;
use yCrawler\Crawler;
use yCrawler\Parser;
use yCrawler\Queue;
use yCrawler\Document;
use yCrawler\Crawler\Thread;
use yCrawler\Crawler\ThreadPool;

class CrawlerTest extends  \PHPUnit_Framework_TestCase
{
    public function createCrawler()
    {
        $this->queue = new Queue();
        $this->pool = new ThreadPool('yCrawler\Crawler\Thread', 5);

        return new Crawler($this->queue, $this->pool);
    }

    public function testAdd()
    {
        $crawler = $this->createCrawler();
        $this->assertTrue($crawler->addParser(new CrawlerTest_ParserMock));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testAddTwice()
    {
        $crawler = $this->createCrawler();
        $crawler->addParser(new CrawlerTest_ParserMock);
        $crawler->addParser(new CrawlerTest_ParserMock);

    }

    public function testHas()
    {
        $crawler = $this->createCrawler();
        $crawler->addParser(new CrawlerTest_ParserMock);

        $this->assertTrue($crawler->hasParser('CrawlerTest_ParserMock'));
    }

    public function testGet()
    {
        $crawler = $this->createCrawler();
        $crawler->addParser(new CrawlerTest_ParserMock);

        $this->assertInstanceOf(
            'yCrawler\Parser',
            $crawler->getParser('CrawlerTest_ParserMock')
        );
    }

    public function testOnParse()
    {
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
        $crawler = $this->createCrawler();
        $crawler->addParser(new CrawlerTest_ParserMock);

        $crawler->run();
    }
}

class CrawlerTest_ParserMock extends Parser
{
    public function initialize()
    {
        $this->setStartupURL('http://httpbin.org/');

        $this->createLinkFollowItem('//a');
        $this->createVerifyItem('//a');

        $this->createValueItem('no-exists', '//no-exists-tag');
        $this->createValueItem('pre', '//pre');
    }
}
