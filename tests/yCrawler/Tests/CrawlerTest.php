<?php
namespace yCrawler\Tests;
use yCrawler\Crawler;
use yCrawler\Parser;
use yCrawler\Queue;
use yCrawler\Document;

class CrawlerTest extends  \PHPUnit_Framework_TestCase { 
    public function createCrawler() {
        $queue = new Queue();
        return new Crawler($queue);
    }

    public function testAdd() {
        $crawler = $this->createCrawler();
        $this->assertTrue($crawler->add(new CrawlerTest_ParserMock));
    }  

    /**
     * @expectedException RuntimeException
     */
    public function testAddTwice() {
        $crawler = $this->createCrawler();
        $crawler->add(new CrawlerTest_ParserMock);
        $crawler->add(new CrawlerTest_ParserMock);

    }  

    public function testHas() {
        $crawler = $this->createCrawler();
        $crawler->add(new CrawlerTest_ParserMock);

        $this->assertTrue($crawler->has('CrawlerTest_ParserMock'));
    }  

    public function testGet() {
        $crawler = $this->createCrawler();
        $crawler->add(new CrawlerTest_ParserMock);

        $this->assertInstanceOf(
            'yCrawler\Parser', 
            $crawler->get('CrawlerTest_ParserMock')
        );
    }  

    public function testOnParse() {
        $parser = new CrawlerTest_ParserMock;

        $crawler = $this->createCrawler();
        $crawler->onParse(function($document) {
            return get_class($document);
        });

        $crawler->add($parser);

        $result = $parser->parsed(new Document('http://test.com'));
        $this->assertSame('yCrawler\Document', $result);
    }
}

class CrawlerTest_ParserMock extends Parser {
    public function initialize() {
        $this->setStartupURL('http://httpbin.org/');

        $this->createLinkFollowItem('//a');
        $this->createVerifyItem('//a');

        $this->createValueItem('no-exists', '//no-exists-tag');
        $this->createValueItem('pre', '//pre');
    }
}