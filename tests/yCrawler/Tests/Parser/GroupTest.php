<?php

namespace yCrawler\Tests;

use yCrawler\Document;
use yCrawler\Parser\Group;
use yCrawler\Parser\Item;
use yCrawler\Parser\Rule\Literal;

class GroupTest extends TestCase
{
    public function testAddModifier()
    {
        $group = new Group;
        $group->addModifier(function() { return 1; });
        $group->addModifier(function() { return 2; });

        $modifiers = $group->getModifiers();

        $this->assertSame(2, count($modifiers));
        $this->assertSame(1, $modifiers[0]());
        $this->assertSame(2, $modifiers[1]());
    }

    public function testEvaluate()
    {
        $doc = $this->createDocumentMock();
        $group = new Group;
        $group->addRule(new Literal('a'));
        $group->addRule(new Literal('b'));

        $result = $group->evaluate($doc);
        $this->assertSame('a', $result[0]['value']);
        $this->assertSame('b', $result[1]['value']);
    }

    public function testEvaluateWithModifier()
    {
        $doc = $this->createDocumentMock();
        $group = new Group;
        $group->addRule(new Literal(1));
        $group->addRule(new Literal(4));

        $group->addModifier(
            function($result)
            {
                $result[0]['value'] += 3;
                $result[1]['value'] += 2;
                return $result;
            }
        );

        $result = $group->evaluate($doc);

        $this->assertSame(4, $result[0]['value']);
        $this->assertSame(6, $result[1]['value']);
    }

    protected function createDocumentMock()
    {
        $document = parent::createDocumentMock();
        $document->shouldReceive('getDOM')
            ->withNoArgs()
            ->twice()
            ->andReturn(new \DOMDocument());

        return $document;
    }
}