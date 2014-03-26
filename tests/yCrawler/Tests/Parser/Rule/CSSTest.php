<?php

namespace yCrawler\Tests\Parser\Rule\Types;

use yCrawler\Tests\Parser\Rule\RuleTestCase;

class CSSTest extends RuleTestCase
{
    const TESTED_CLASS = 'yCrawler\Parser\Rule\CSS';

    const EXAMPLE_PATTERN_INPUT = 'div.item > h4 > a';
    const EXAMPLE_PATTERN_OUTPUT = "descendant-or-self::div[@class and contains(concat(' ', normalize-space(@class), ' '), ' item ')]/h4/a";
    const EXAMPLE_RESULT = 'foo';

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
