<?php
namespace yCrawler\Tests;
use yCrawler\Document;
use yCrawler\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $parser = new ParserTest_ParserMock();
        $this->assertTrue($parser->configure());
        $this->assertTrue($parser->configure());
    }

    public function testSetStartupURL()
    {
        $url = 'http://test.com/';

        $parser = new ParserTest_ParserMock();
        $this->assertSame($url, $parser->setStartupURL($url));
        $this->assertSame(1, count($parser->getStartupDocs()));
    }

    public function testSetURLPattern()
    {
        $regexp = '/httpbin/';

        $parser = new ParserTest_ParserMock();
        $this->assertSame($regexp, $parser->setURLPattern($regexp));
        $this->assertSame(1, count($parser->getURLPatterns()));
    }

    public function testMatchURL()
    {
        $url = 'http://test.com/';
        $regexp = '/httpbin/';

        $parser = new ParserTest_ParserMock();
        $this->assertFalse($parser->matchURL('http://test.com/'));
    }

    public function testMatchURLDefault()
    {
        $url = 'http://test.com/';
        $regexp = '/httpbin/';

        $parser = new ParserTest_ParserMock();
        $parser->setStartupURL($url);
        $this->assertTrue($parser->matchURL('http://test.com/'));

        $patterns = $parser->getURLPatterns();
        $this->assertSame(1, count($patterns));
        $this->assertSame('~^https?://test\.com~', $patterns[0]);
    }

    public function testCreateLinkFollowItem()
    {
        $pattern = '//a';

        $parser = new ParserTest_ParserMock();
        $item = $parser->createLinkFollowItem($pattern, false);
        $item = $parser->createLinkFollowItem($pattern, true);
        $item = $parser->createLinkFollowItem($pattern);

        $this->assertSame($pattern, $item->getPattern());
        $this->assertInstanceOf('yCrawler\Parser\Item', $item);

        $items = $parser->getFollowItems();
        $this->assertInstanceOf('yCrawler\Parser\Item', $items[0][0]);
        $this->assertFalse($items[0][1]);
        $this->assertTrue($items[1][1]);
        $this->assertTrue($items[2][1]);
    }

    public function testCreateVerifyItem()
    {
        $pattern = '//a';

        $parser = new ParserTest_ParserMock();
        $item = $parser->createVerifyItem($pattern, false);
        $item = $parser->createVerifyItem($pattern, true);
        $item = $parser->createVerifyItem($pattern);

        $this->assertSame($pattern, $item->getPattern());
        $this->assertInstanceOf('yCrawler\Parser\Item', $item);

        $items = $parser->getVerifyItems();
        $this->assertInstanceOf('yCrawler\Parser\Item', $items[0][0]);
        $this->assertFalse($items[0][1]);
        $this->assertTrue($items[1][1]);
        $this->assertTrue($items[2][1]);
    }

    public function testCreateLinksItem()
    {
        $pattern = '//a';

        $parser = new ParserTest_ParserMock();
        $item = $parser->createLinksItem($pattern);
        $item = $parser->createLinksItem($pattern);

        $this->assertSame($pattern, $item->getPattern());
        $this->assertInstanceOf('yCrawler\Parser\Item', $item);

        $items = $parser->getLinksItems();
        $this->assertInstanceOf('yCrawler\Parser\Item', $items[0]);
        $this->assertSame(2, count($items));

        $item = $parser->createLinksItem($pattern, true);
        $items = $parser->getLinksItems();
        $this->assertSame(1, count($items));
    }

    public function testCreateValueItem()
    {
        $pattern = '//a';

        $parser = new ParserTest_ParserMock();
        $item = $parser->createValueItem('test', $pattern);

        $this->assertSame($pattern, $item->getPattern());
        $this->assertInstanceOf('yCrawler\Parser\Item', $item);

        $items = $parser->getValueItems();
        $this->assertTrue(isset($items['test']));
        $this->assertInstanceOf('yCrawler\Parser\Item', $items['test']);
    }

    public function testCreateValueGroup()
    {
        $pattern = '//a';

        $parser = new ParserTest_ParserMock();
        $item = $parser->createValueGroup('test');
        $this->assertInstanceOf('yCrawler\Parser\Group', $item);

        $items = $parser->getValueItems();
        $this->assertTrue(isset($items['test']));
        $this->assertInstanceOf('yCrawler\Parser\Group', $items['test']);
    }

    public function testOnParse()
    {
        $parser = new ParserTest_ParserMock();
        $parser->onParse(function($document) {
            return get_class($document);
        });

        $result = $parser->parsed(new Document('http://test.com'));
        $this->assertSame('yCrawler\Document', $result);
    }
}

class ParserTest_ParserMock extends Parser
{
    public function initialize()
    {
    }
}
