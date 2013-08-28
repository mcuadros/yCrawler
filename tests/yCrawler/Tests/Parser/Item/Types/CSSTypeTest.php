<?php
namespace yCrawler\Tests\Parser\Item\Types;
use yCrawler\Parser\Item\Types;
use yCrawler\Tests\Testcase;

class CSSTypeTest extends Type
{
    const TESTED_CLASS = 'yCrawler\Parser\Item\Types\CSSType';

    const EXAMPLE_PATTERN_INPUT = 'div.item > h4 > a';
    const EXAMPLE_PATTERN_OUTPUT = "descendant-or-self::div[@class and contains(concat(' ', normalize-space(@class), ' '), ' item ')]/h4/a";
    const EXAMPLE_RESULT = 'foo';
}
