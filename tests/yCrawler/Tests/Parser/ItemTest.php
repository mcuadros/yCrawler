<?php

namespace yCrawler\Tests;

use yCrawler\Document;
use yCrawler\Parser\Item;

class ItemTest extends TestCase
{
    const INVALID_TYPE = 'not-valid';
    const EXAMPLE_PATTERN = 'foo';

    public function testGetTypeDefaultType()
    {
        $item = new Item;
        $this->assertSame(Item::TYPE_XPATH, $item->getType());
    }

    public function testSetTypeAndGetType()
    {
        $item = new Item;
        $item->setType(Item::TYPE_REGEXP);
        $this->assertSame(Item::TYPE_REGEXP, $item->getType());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetTypeInvalid()
    {
        $item = new Item;
        $item->setType(self::INVALID_TYPE);
    }

    public function testSetPatternAndGetPattern()
    {
        $item = new Item;
        $item->setPattern(self::EXAMPLE_PATTERN);
        $this->assertSame(self::EXAMPLE_PATTERN, $item->getPattern());
    }

    public function testSetModifier()
    {
        $item = new Item;
        $item->setModifier(function() { return 1; });
        $item->setModifier(function() { return 2; });

        $modifiers = $item->getModifiers();

        $this->assertSame(2, count($modifiers));
        $this->assertSame(1, $modifiers[0]());
        $this->assertSame(2, $modifiers[1]());
    }

    public function testEvaluate()
    {
        $item = new Item;
        $item->setType(Item::TYPE_LITERAL);
        $item->setPattern(self::EXAMPLE_PATTERN);

        $doc = $this->createDocumentMock();
        $result = $item->evaluate($doc);

        $this->assertCount(1, $result);
        $this->assertSame(self::EXAMPLE_PATTERN, $result[0]['value']);
    }

    protected function createDocumentMock()
    {
        $document = parent::createDocumentMock();
        $document->shouldReceive('getDOM')
            ->withNoArgs()
            ->once()
            ->andReturn(new \DOMDocument());

        return $document;
    }
}