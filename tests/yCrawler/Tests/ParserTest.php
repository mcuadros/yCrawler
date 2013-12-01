<?php

namespace yCrawler\Tests;

use yCrawler\Document;
use yCrawler\Parser;
use yCrawler\Parser\Item;
use yCrawler\Parser\Group;

class ParserTest extends TestCase
{
    const EXAMPLE_URL_A = 'http://foo.com/';
    const EXAMPLE_URL_B = 'http://bar.com/';
    const EXAMPLE_URL_MALFORMED = null;
    const EXAMPLE_PATTERN = '/foo/';
    const EXAMPLE_PATTERN_DOMAIN_BASED = '~^https?://foo\.com~';
    const EXAMPLE_PATTERN_MALFORMED = 'dsds';

    public function testConfigure()
    {
        $parser = $this->createParserMock();
        $this->assertFalse($parser->isInitialized());

        $parser->configure();
        $this->assertTrue($parser->isInitialized());
    }
    
    public function testSetStartupURL()
    {
        $parser = $this->createParserMock();

        $parser->setStartupURL(self::EXAMPLE_URL_A);
        $this->assertCount(1, $parser->getStartupURLs());
        $this->assertSame([self::EXAMPLE_URL_A], $parser->getStartupURLs());

        $parser->setStartupURL(self::EXAMPLE_URL_B);
        $this->assertCount(2, $parser->getStartupURLs());
        $this->assertSame(
            [self::EXAMPLE_URL_A, self::EXAMPLE_URL_B], 
            $parser->getStartupURLs()
        );
    }

    public function testClearStartupURLs()
    {
        $parser = $this->createParserMock();
        $parser->setStartupURL(self::EXAMPLE_URL_A);

        $parser->clearStartupURLs();
        $this->assertCount(0, $parser->getStartupURLs());
    }

    /**
     * @expectedException yCrawler\Parser\Exceptions\InvalidStartupURL
     */
    public function testSetStartupURLInvalid()
    {
        $parser = $this->createParserMock();
        $parser->setStartupURL(self::EXAMPLE_URL_MALFORMED);
    }

    public function testGetStartupDocs()
    {
        $parser = $this->createParserMock();
        $parser->setStartupURL(self::EXAMPLE_URL_A);

        $result = $parser->getStartupDocs();
        $document = current($result);

        $this->assertSame(self::EXAMPLE_URL_A, $document->getURL());
        $this->assertTrue($parser->isInitialized());
    }
    
    public function testSetURLPattern()
    {
        $parser = $this->createParserMock();
        $parser->setURLPattern(self::EXAMPLE_PATTERN);

        $this->assertCount(1, $parser->getURLPatterns());
        $this->assertSame([self::EXAMPLE_PATTERN], $parser->getURLPatterns());
    }

    /**
     * @expectedException yCrawler\Parser\Exceptions\InvalidURLPattern
     */
    public function testSetURLPatternInvalid()
    {
        $parser = $this->createParserMock();
        $parser->setURLPattern(self::EXAMPLE_PATTERN_MALFORMED);
    }


    public function testMatchURL()
    {
        $parser = $this->createParserMock();

        $this->assertFalse($parser->matchURL(self::EXAMPLE_URL_A));

        $parser->setURLPattern(self::EXAMPLE_PATTERN);
        $this->assertTrue($parser->matchURL(self::EXAMPLE_URL_A));
    }

    public function testMatchURLDefault()
    {
        $parser = $this->createParserMock();
        $parser->setStartupURL(self::EXAMPLE_URL_A);
        $this->assertTrue($parser->matchURL(self::EXAMPLE_URL_A));
        $this->assertFalse($parser->matchURL(self::EXAMPLE_URL_B));

        $patterns = $parser->getURLPatterns();
        $this->assertCount(1, $patterns);
        $this->assertSame(
            [self::EXAMPLE_PATTERN_DOMAIN_BASED], 
            $patterns
        );
    }

    public function testAddLinkFollowItem()
    {
        $parser = $this->createParserMock();

        $item = new Item();

        $parser->addLinkFollowItem($item, false);
        $this->assertSame([
            [$item, false]
        ], $parser->getFollowItems());

        $parser->addLinkFollowItem($item, true);
        $this->assertSame([
            [$item, false],
            [$item, true]
        ], $parser->getFollowItems());

        $parser->clearFollowItems();
        $parser->addLinkFollowItem($item, true);
        $this->assertSame([
            [$item, true]
        ], $parser->getFollowItems());
    }

    public function testCreateLinkFollowItem()
    {
        $pattern = '//a';

        $parser = $this->createParserMock();
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

    public function testAddVerifyItem()
    {
        $parser = $this->createParserMock();

        $item = new Item();

        $parser->addVerifyItem($item, false);
        $this->assertSame([
            [$item, false]
        ], $parser->getVerifyItems());

        $parser->addVerifyItem($item, true);
        $this->assertSame([
            [$item, false],
            [$item, true]
        ], $parser->getVerifyItems());

        $parser->clearVerifyItems();
        $parser->addVerifyItem($item, true);
        $this->assertSame([
            [$item, true]
        ], $parser->getVerifyItems());
    }

    public function testCreateVerifyItem()
    {
        $pattern = '//a';

        $parser = $this->createParserMock();
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

    public function testAddLinksItem()
    {
        $parser = $this->createParserMock();

        $item = new Item();

        $parser->addLinksItem($item);
        $this->assertSame([$item], $parser->getLinksItems());

        $parser->addLinksItem($item);
        $this->assertSame([$item, $item], $parser->getLinksItems());

        $parser->clearLinksItems();
        $parser->addLinksItem($item);
        $this->assertSame([$item], $parser->getLinksItems());
    }

    public function testCreateLinksItem()
    {
        $pattern = '//a';

        $parser = $this->createParserMock();
        $item = $parser->createLinksItem($pattern);
        $item = $parser->createLinksItem($pattern);

        $this->assertSame($pattern, $item->getPattern());
        $this->assertInstanceOf('yCrawler\Parser\Item', $item);

        $items = $parser->getLinksItems();
        $this->assertInstanceOf('yCrawler\Parser\Item', $items[0]);
        $this->assertSame(2, count($items));

        $item = $parser->createLinksItem($pattern);
        $items = $parser->getLinksItems();
        $this->assertSame(3, count($items));
    }

    public function testAddValueItem()
    {
        $parser = $this->createParserMock();

        $item = new Item();

        $parser->addValueItem('foo', $item);
        $this->assertSame([
            'foo' => $item
        ], $parser->getValueItems());

        $parser->addValueItem('bar', $item);
        $this->assertSame([
            'foo' => $item,
            'bar' => $item
        ], $parser->getValueItems());

        $parser->clearValueItems();
        $parser->addValueItem('bar', $item);
        $this->assertSame([
            'bar' => $item
        ], $parser->getValueItems());
    }

    public function testCreateValueItem()
    {
        $pattern = '//a';

        $parser = $this->createParserMock();
        $item = $parser->createValueItem('test', $pattern);

        $this->assertSame($pattern, $item->getPattern());
        $this->assertInstanceOf('yCrawler\Parser\Item', $item);

        $items = $parser->getValueItems();
        $this->assertTrue(isset($items['test']));
        $this->assertInstanceOf('yCrawler\Parser\Item', $items['test']);
    }

    public function testAddGroupItem()
    {
        $parser = $this->createParserMock();

        $group = new Group();

        $parser->addValueGroup('foo', $group);
        $this->assertSame([
            'foo' => $group
        ], $parser->getValueItems());

        $parser->addValueGroup('bar', $group);
        $this->assertSame([
            'foo' => $group,
            'bar' => $group
        ], $parser->getValueItems());

        $parser->clearValueItems();
        $parser->addValueGroup('bar', $group);
        $this->assertSame([
            'bar' => $group
        ], $parser->getValueItems());
    }

    public function testCreateValueGroup()
    {
        $pattern = '//a';

        $parser = $this->createParserMock();
        $item = $parser->createValueGroup('test');
        $this->assertInstanceOf('yCrawler\Parser\Group', $item);

        $items = $parser->getValueItems();
        $this->assertTrue(isset($items['test']));
        $this->assertInstanceOf('yCrawler\Parser\Group', $items['test']);
    }

    public function testSetOnParseCallback()
    {
        $closure = function($document) {
            return get_class($document);
        };

        $parser = $this->createParserMock();
        $parser->setOnParseCallback($closure);

        $this->assertSame($closure, $parser->getOnParseCallback());
    }
}