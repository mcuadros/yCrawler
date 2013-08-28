<?php
namespace yCrawler\Tests;
use yCrawler\Document;
use Mockery as m;

class DocumentTest extends TestCase
{
    public function createDocument($url)
    {
        $parser = $this->createParserMock();

        return new Document($url, $parser);
    }

    public function testGetURL()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $this->assertSame($url, $doc->getURL());
    }

    public function testGetParser()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $this->assertInstanceOf(
            'yCrawler\Mocks\ParserMock',
            $doc->getParser()
        );
    }

    public function testGetHTML()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $this->assertTrue(strlen($doc->getHTML()) > 0);
    }

    public function testGetDOM()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $this->assertInstanceOf('DOMDocument', $doc->getDOM());
        $this->assertTrue(strlen($doc->getDOM()->saveHTML()) > 0);
    }

    public function testGetXPath()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $this->assertInstanceOf('DOMXPath', $doc->getXPath());
    }

    public function testIsVerified()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $this->assertTrue($doc->isVerified());
    }

    public function testIsIndexable()
    {
        $url = 'http://httpbin.org/';

        $doc = $this->createDocument($url);
        $this->assertTrue($doc->isIndexable());
    }

    public function testGetLinksStorage()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $this->assertCount(26, $doc->getLinksStorage()->all());
    }

    public function testGetValuesStorage()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $this->assertCount(4, $doc->getValuesStorage()->get('pre'));
    }
}