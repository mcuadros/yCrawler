<?php
namespace yCrawler\Tests\Parser\Item\Types;
use yCrawler\Parser\Item\Types;
use yCrawler\Tests\Testcase;

class Type extends TestCase
{
    protected $emptyNode = false;

    public function testEvaluate()
    {
        $document = $this->createDocumentMock();

        $class = static::TESTED_CLASS;
        $type = new $class();

        $result = $type->evaluate($document, static::EXAMPLE_PATTERN_INPUT);

        $this->assertSame(static::EXAMPLE_RESULT, $result[0]['value']);
        
        if ($this->emptyNode) {
            $this->assertNull($result[0]['node']);
        } else {
            $this->assertInstanceOf('StdClass', $result[0]['node']);
        }

        $this->assertInstanceOf('DOMDocument', $result[0]['dom']);        
    }
}
