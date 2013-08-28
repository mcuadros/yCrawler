<?php
namespace yCrawler\Tests;
use yCrawler\Document;
use yCrawler\Parser\Group;
use yCrawler\Parser\Item;

class GroupTest extends TestCase
{
    public function testCreateItem()
    {
        $group = new Group;
        $this->assertInstanceOf('yCrawler\Parser\Item', $group->createItem());
    }

    public function testSetModifier()
    {
        $group = new Group;
        $group->setModifier(function() { return 1; });
        $group->setModifier(function() { return 2; });

        $modifiers = $group->getModifiers();

        $this->assertSame(2, count($modifiers));
        $this->assertSame(1, $modifiers[0]());
        $this->assertSame(2, $modifiers[1]());
    }

    public function testEvaluate()
    {
        $doc = $this->createDocumentMock();
        $group = new Group;
        $group->createItem('a')->setType(Item::TYPE_LITERAL);
        $group->createItem('b')->setType(Item::TYPE_LITERAL);

        $result = $group->evaluate($doc);
        $this->assertSame('a', $result[0]['value']);
        $this->assertSame('b', $result[1]['value']);
    }

    public function testEvaluateWithModifier()
    {
        $doc = $this->createDocumentMock();
        $group = new Group;
        $group->createItem(1)->setType(Item::TYPE_LITERAL);
        $group->createItem(4)->setType(Item::TYPE_LITERAL);

        $group->setModifier(function(&$result) {
            $result[0]['value'] += 3;
            $result[1]['value'] += 2;
        });

        $result = $group->evaluate($doc);

        $this->assertSame(4, $result[0]['value']);
        $this->assertSame(6, $result[1]['value']);
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