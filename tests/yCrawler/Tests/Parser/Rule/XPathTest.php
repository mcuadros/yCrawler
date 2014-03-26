<?php

namespace yCrawler\Tests\Parser\Rule;

use yCrawler\Tests\Parser\Rule\RuleTestCase;

class XPathTest extends RuleTestCase
{
    const TESTED_CLASS = 'yCrawler\Parser\Rule\XPath';

    const EXAMPLE_PATTERN_INPUT = '/foo/';
    const EXAMPLE_PATTERN_OUTPUT = '/foo/';
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
