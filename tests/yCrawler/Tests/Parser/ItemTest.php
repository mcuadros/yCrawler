<?php
namespace yCrawler\Tests;
use yCrawler\Document;
use yCrawler\Parser\Item;

class ItemTest extends  \PHPUnit_Framework_TestCase
{
    public function testDefaultType()
    {
        $item = new Item;
        $this->assertSame(Item::TYPE_XPATH, $item->getType());
    }

    public function testSetType()
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
        $item->setType('not-valid');
    }

    public function testSetPattern()
    {
        $pattern = '//a';

        $item = new Item;
        $item->setPattern($pattern);
        $this->assertSame($pattern, $item->getPattern());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetPatternRegExpInvalid()
    {
        $item = new Item;
        $item->setType(Item::TYPE_REGEXP);

        $item->setPattern('aaaaa');
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

    public function testEvaluateXPath()
    {
        $item = new Item;
        $item->setPattern('//a');

        $doc = new ItemTest_DocumentMock(false);
        $result = $item->evaluate($doc);

        $this->assertSame(1, $result[0]['value']);
    }

    public function testEvaluateRegExp()
    {
        $item = new Item;
        $item->setType(Item::TYPE_REGEXP);
        $item->setPattern('/a/');

        $doc = new ItemTest_DocumentMock(false);
        $result = $item->evaluate($doc);

        $this->assertSame(2, $result[0]['value']);
    }

    public function testEvaluateXPathWithModifier()
    {
        $item = new Item;
        $item->setPattern('//a');
        $item->setModifier(function(&$result) { $result[0]['value'] += 2; });

        $doc = new ItemTest_DocumentMock(false);
        $result = $item->evaluate($doc);

        $this->assertSame(3, $result[0]['value']);
    }
}

class ItemTest_DocumentMock extends Document
{
    public function evaluateXPath($pattern)
    {
        return Array(
            Array('value' => 1)
        );
    }

    public function evaluateRegExp($pattern)
    {
        return Array(
            Array('value' => 2)
        );
    }
}
