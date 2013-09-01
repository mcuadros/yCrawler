<?php
namespace yCrawler\Tests;
use yCrawler\Document;
use Mockery as m;

class DocumentTest extends TestCase
{
    const EXAMPLE_URL = 'http://httpbin.org/';

    public function createDocument($url)
    {
        $parser = $this->createParserMock();

        return new Document($url, $parser);
    }

    public function testGetURL()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);

        $this->assertSame(self::EXAMPLE_URL, $doc->getURL());
    }

    public function testGetParser()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);

        $this->assertInstanceOf(
            'yCrawler\Mocks\ParserMock',
            $doc->getParser()
        );
    }

    public function testGetHTML()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);
        $doc->parse();

        $this->assertTrue(strlen($doc->getHTML()) > 0);
    }

    public function testGetDOM()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);
        $doc->parse();

        $this->assertInstanceOf('DOMDocument', $doc->getDOM());
        $this->assertTrue(strlen($doc->getDOM()->saveHTML()) > 0);
    }

    public function testGetXPath()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);
        $doc->parse();

        $this->assertInstanceOf('DOMXPath', $doc->getXPath());
    }

    public function testIsVerified()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);
        $doc->parse();

        $this->assertTrue($doc->isVerified());
    }

    public function testIsIndexable()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);
        $doc->parse();

        $this->assertTrue($doc->isIndexable());
    }

    public function testGetLinksStorage()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);
        $doc->parse();

        $this->assertCount(25, $doc->getLinksStorage()->all());
    }

    public function testGetValuesStorage()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);
        $doc->parse();

        $this->assertCount(4, $doc->getValuesStorage()->get('pre'));
    }

    public function testParseAndIsParsed()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);

        $this->assertNull($doc->isParsed());

        $doc->parse();
        $this->assertTrue($doc->isParsed());
    }

    public function testCallOnParseCallback()
    {
        $doc = $this->createDocument(self::EXAMPLE_URL);

        $parser = $doc->getParser();
        $called = false;
        $parser->setOnParseCallback(function() use (&$called) {
            $called = true;
        });

        $doc->parse();
        $this->assertTrue($called);
    }
}