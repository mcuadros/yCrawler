<?php

namespace yCrawler\Tests\Parser\Rule;

use yCrawler\Parser\Item\Types;
use yCrawler\Tests\TestCase;

abstract class RuleTestCase extends TestCase
{
    public function testEvaluate()
    {
        $document = $this->createDocumentMock();

        $class = static::TESTED_CLASS;
        $rule = new $class(static::EXAMPLE_PATTERN_INPUT);

        $result = $rule->evaluate($document);

        $this->assertSame(static::EXAMPLE_RESULT, $result[0]['value']);

        $this->assertNode($result);
        $this->assertInstanceOf('DOMDocument', $result[0]['dom']);
    }

    protected function assertNode($result)
    {
        $this->assertInstanceOf('StdClass', $result[0]['node']);
    }
}