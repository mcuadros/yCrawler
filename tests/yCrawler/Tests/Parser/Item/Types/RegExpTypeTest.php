<?php
namespace yCrawler\Tests\Parser\Item\Types;
use yCrawler\Parser\Item\Types;
use yCrawler\Tests\Testcase;

class RegExpTypeTest extends Type
{
    const TESTED_CLASS = 'yCrawler\Parser\Item\Types\RegExpType';

    const EXAMPLE_PATTERN_INPUT = '/f([a-z]*)/';
    const EXAMPLE_PATTERN_OUTPUT = null;
    const EXAMPLE_RESULT = 'oo';
    const EXAMPLE_RESULT_FULL = 'foo';
    const EXAMPLE_HTML = 'foo';

    protected $testDOM = false;
    
    public function testEvaluate()
    {
        $document = $this->createDocumentMock();

        $class = static::TESTED_CLASS;
        $type = new $class();

        $result = $type->evaluate($document, static::EXAMPLE_PATTERN_INPUT);

        $this->assertSame(static::EXAMPLE_RESULT, $result[0]['value']);
        $this->assertSame(static::EXAMPLE_RESULT_FULL, $result[0]['full']);

        $this->assertInstanceOf('DOMDocument', $result[0]['dom']);        
    }

    protected function createDocumentMock()
    {
        $document = parent::createDocumentMock();
        $document->shouldReceive('getHTML')
            ->withNoArgs()
            ->once()
            ->andReturn(self::EXAMPLE_HTML);

        return $document;
    }
}
