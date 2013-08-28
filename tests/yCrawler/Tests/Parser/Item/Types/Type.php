<?php
namespace yCrawler\Tests\Parser\Item\Types;
use yCrawler\Parser\Item\Types;
use yCrawler\Tests\Testcase;

class Type extends TestCase
{
    public function testEvaluate()
    {
        $document = $this->createDocumentMock();

        $class = static::TESTED_CLASS;
        $type = new $class();

        $result = $type->evaluate($document, static::EXAMPLE_PATTERN_INPUT);

        $this->assertSame(static::EXAMPLE_RESULT, $result[0]['value']);
        $this->assertInstanceOf('StdClass', $result[0]['node']);
        $this->assertInstanceOf('DOMDocument', $result[0]['dom']);        
    }

    protected function createDocumentMock()
    {
        $node = (object) ['nodeValue' => static::EXAMPLE_RESULT];

        $xpath = $this->createXPathMock();
        $xpath->shouldReceive('evaluate')
            ->with(static::EXAMPLE_PATTERN_OUTPUT)
            ->once()
            ->andReturn([$node]);

        $document = parent::createDocumentMock();
        $document->shouldReceive('getXPath')
            ->withNoArgs()
            ->once()
            ->andReturn($xpath);

        $document->shouldReceive('getDOM')
            ->withNoArgs()
            ->once()
            ->andReturn(new \DOMDocument());

        return $document;
    }

}
