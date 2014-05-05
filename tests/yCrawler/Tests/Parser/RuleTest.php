<?php

namespace yCrawler\Tests;

use yCrawler\Document;
use yCrawler\Parser\Rule\Literal;
use yCrawler\Parser\Rule\XPath;

class ItemTest extends TestCase
{
    const INVALID_TYPE = 'not-valid';
    const EXAMPLE_PATTERN = 'foo';

    public function testGetPattern()
    {
        $rule = new XPath(self::EXAMPLE_PATTERN);
        $this->assertSame(self::EXAMPLE_PATTERN, $rule->getPattern());
    }

    public function testSetModifier()
    {
        $rule = new XPath(self::EXAMPLE_PATTERN);
        $rule->addModifier(function() { return 1; });
        $rule->addModifier(function() { return 2; });

        $modifiers = $rule->getModifiers();

        $this->assertSame(2, count($modifiers));
        $this->assertSame(1, $modifiers[0]());
        $this->assertSame(2, $modifiers[1]());
    }

    public function testEvaluate()
    {
        $rule = new Literal(self::EXAMPLE_PATTERN);

        $doc = $this->createDocumentMock();
        $result = $rule->evaluate($doc);

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