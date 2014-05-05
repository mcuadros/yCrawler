<?php

namespace yCrawler\Tests\Parser\Rule\Types;

use yCrawler\Tests\Parser\Rule\RuleTestCase;

class LiteralTest extends RuleTestCase
{
    protected $emptyNode = true;

    const TESTED_CLASS = 'yCrawler\Parser\Rule\Literal';

    const EXAMPLE_PATTERN_INPUT = 'foo';
    const EXAMPLE_PATTERN_OUTPUT = 'foo';
    const EXAMPLE_RESULT = 'foo';

    protected function assertNode($result)
    {
        $this->assertNull($result[0]['node']);
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
