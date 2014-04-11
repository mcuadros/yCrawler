<?php

namespace yCrawler\Tests;

use yCrawler\Crawler;
use yCrawler\Mocks\ParserMock;
use yCrawler\Crawler\Runner\BasicRunner;
use yCrawler\Crawler\Queue\SimpleQueue;
use yCrawler\Document;
use \Mockery as m;
use yCrawler\Parser\Rule\XPath;
use yCrawler\Parser;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    const EXAMPLE_MARKUP = '<html><body><pre><a href="foo">bar</a></pre></body></html>';

    public function testRun()
    {
        $parser = new ParserMock('mock');
        $doc = new Document('http://aurl', $parser);
        $crawler = $this->getCrawler($doc);
        $crawler->run(0);
        $this->assertTrue($doc->isParsed());
    }

    public function testOverrideOnParse()
    {
        $parser = new ParserMock('mock');
        $doc = new Document('http://aurl', $parser);
        $override = false;
        $crawler = $this->getCrawler($doc);
        $crawler->overrideOnParse(
            function ($document) use (&$override) {
                $override = true;
            }
        );
        $crawler->run(0);
        $this->assertTrue($override);
    }

    public function testRequeueLinkableDocs()
    {
        $passes = 0;
        $parser = new Parser('mock');
        $parser->addLinkFollowRule(new XPath('//a'), true);
        $parser->addValueRule(new XPath('//no-exists-tag'), 'no-exists');
        $parser->addValueRule(new XPath('//pre'), 'pre');

        $doc = new Document('http://aurl', $parser);
        $doc->setMarkup(self::EXAMPLE_MARKUP);
        $crawler = $this->getCrawler($doc);
        $crawler->overrideOnParse(
            function ($document) use (&$passes) {
                $passes++;
            }
        );
        $crawler->run(0);
        $this->assertEquals(2, $passes);
    }

    protected function getCrawler($doc)
    {
        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->andReturn(m::self());
        $client->shouldReceive('getBody')->andReturnValues([self::EXAMPLE_MARKUP, '<html></html>']);

        $runner = new BasicRunner($client);
        $queue = new SimpleQueue();
        $queue->add($doc);

        $crawler = new Crawler($queue, $runner);
        return $crawler;
    }
}
