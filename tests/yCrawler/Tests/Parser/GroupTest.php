<?php
namespace yCrawler\Tests;
use yCrawler\Document;
use yCrawler\Parser\Group;
use yCrawler\Parser\Item;

class GroupTest extends  \PHPUnit_Framework_TestCase { 
    public function testCreateItem() {
        $group = new Group;
        $this->assertInstanceOf('yCrawler\Parser\Item', $group->createItem());
    }

    public function testSetModifier() {
        $group = new Group;
        $group->setModifier(function() { return 1; });
        $group->setModifier(function() { return 2; });

        $modifiers = $group->getModifiers();

        $this->assertSame(2, count($modifiers));
        $this->assertSame(1, $modifiers[0]());
        $this->assertSame(2, $modifiers[1]());
    }

    public function testEvaluate() {
        $doc = new GroupTest_DocumentMock('http://test.com');
        $group = new Group;
        $group->createItem('a');
        $group->createItem('b')->setType(Item::TYPE_REGEXP);

        $result = $group->evaluate($doc);
        $this->assertSame(1, $result[0]['value']);
        $this->assertSame(4, $result[1]['value']);
        $this->assertSame(2, $result[2]['value']);

    }

    public function testEvaluateWithModifier() {
        $doc = new GroupTest_DocumentMock('http://test.com');
        $group = new Group;
        $group->createItem('a');
        $group->setModifier(function(&$result) { 
            $result[0]['value'] += 3; 
            $result[1]['value'] += 2; 
        });

        $result = $group->evaluate($doc);

        $this->assertSame(4, $result[0]['value']);
        $this->assertSame(6, $result[1]['value']);
    }
}




class GroupTest_DocumentMock extends Document {
    public function evaluateXPath($pattern) {
        return Array(
            Array('value' => 1),
            Array('value' => 4)
        );
    }

    public function evaluateRegExp($pattern) {
        return Array(
            Array('value' => 2)
        );
    }
}


