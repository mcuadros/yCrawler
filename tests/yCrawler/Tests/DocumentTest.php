<?php
namespace yCrawler\Tests;
use yCrawler\Document;
use yCrawler\Parser;

class DocumentTest extends  \PHPUnit_Framework_TestCase
{
    public function createDocument($url)
    {
        $parser = new DocumentTest_ParserMock();

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
            'yCrawler\Tests\DocumentTest_ParserMock',
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

    /*
    public function testEvaluateXPathNode()
    {
        $url = 'http://httpbin.org/';

        $doc = $this->createDocument($url);
        $this->assertSame(4, count($doc->evaluateXPath('//pre')));
        $this->assertSame(1, count($doc->evaluateXPath('//pre[1]')));

        $result = $doc->evaluateXPath('//pre[1]');
        $this->assertTrue(isset($result[0]['value']));
        $this->assertTrue(isset($result[0]['node']));
        $this->assertTrue(isset($result[0]['dom']));

        $this->assertSame('{"origin": "24.127.96.129"}' . PHP_EOL, $result[0]['value']);
    }

    public function testEvaluateRegExp()
    {
        $url = 'http://httpbin.org/';

        $doc = $this->createDocument($url);
        $result = $doc->evaluateRegExp('/<code>(.*)<\/code>/');
        $this->assertSame(28, count($result));

        $this->assertTrue(isset($result[0]['value']));
        $this->assertTrue(isset($result[0]['full']));
    }
    */
}

class DocumentTest_ParserMock extends Parser
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
