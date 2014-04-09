<?php

namespace yCrawler\Tests\Parser\Rule;

use yCrawler\Parser\Item\Types;
use yCrawler\Tests\TestCase;

abstract class RuleTestCase extends TestCase
{
    public function testEvaluate()
    {
        $document = $this->createDocumentMock();
        $document->shouldReceive('getDOM')->andReturn($document);
        $document->shouldReceive('saveXML');

        $class = static::TESTED_CLASS;
        $rule = new $class(static::EXAMPLE_PATTERN_INPUT);

        $result = $rule->evaluate($document);

        $this->assertValue($result);
        $this->assertSame(static::EXAMPLE_RESULT, $result[0]['value']);
    }

    protected function assertValue($result)
    {
        $this->assertArrayHasKey('value', $result[0]);
    }
}
