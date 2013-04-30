<?php
namespace yCrawler\Tests\Parse\Item\Modifiers;
use yCrawler\Parser\Item\Modifiers\HTML;
use yCrawler\Document;

class HTMLTest extends  \PHPUnit_Framework_TestCase { 
    function testBoolean() {
        $dom = new \DOMDocument();
        $dom->loadHTML('<html><a href="link">test</a></html>');
        $node = $dom->getElementsByTagName('a')->item(0);

        $closure = HTML::markup();
        $result = array(
            array('value' => 'test', 'dom' => $dom, 'node' => $node)
        );

        $closure($result);
        $this->assertSame('<a href="link">test</a>', $result[0]['value']);
    }

    function testBR2NL() {
        $dom = new \DOMDocument();
        $dom->loadHTML('<html><p>test<br>test<br/>test<br />test</p></html>');
        $node = $dom->getElementsByTagName('p')->item(0);

        $closure = HTML::br2nl();
        $result = array(
            array('value' => 'test', 'dom' => $dom, 'node' => $node)
        );

        $closure($result);
        $this->assertSame(4, count(explode(PHP_EOL, $result[0]['value'])));
    }

    function testImageFromSRC() {
        $dom = new \DOMDocument();
        $dom->loadHTML('<html><img src="test.jpg" /></html>');
        $node = $dom->getElementsByTagName('img')->item(0);

        $document = new Document('http://test.com/url.html');

        $closure = HTML::image();
        $result = array(
            array('value' => 'test', 'dom' => $dom, 'node' => $node)
        );

        $closure($result, $document);
        $this->assertSame('http://test.com/test.jpg', $result[0]['value']);
    }

    function testImageFromHREF() {
        $dom = new \DOMDocument();
        $dom->loadHTML('<html><a href="http://other.com/test2.jpg" /></html>');
        $node = $dom->getElementsByTagName('a')->item(0);

        $document = new Document('http://test.com/url.html');

        $closure = HTML::image();
        $result = array(
            array('value' => 'test', 'dom' => $dom, 'node' => $node)
        );

        $closure($result, $document);
        $this->assertSame('http://other.com/test2.jpg', $result[0]['value']);
    }

    function testImageFromStyle() {
        $dom = new \DOMDocument();
        $dom->loadHTML('<html><b style="background:url(\'test2.jpg\')" /></html>');
        $node = $dom->getElementsByTagName('b')->item(0);

        $document = new Document('http://test.com/url.html');

        $closure = HTML::image();
        $result = array(
            array('value' => 'test', 'dom' => $dom, 'node' => $node)
        );

        $closure($result, $document);
        $this->assertSame('http://test.com/test2.jpg', $result[0]['value']);
    }
}