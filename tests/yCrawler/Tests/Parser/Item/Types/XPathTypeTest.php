<?php
namespace yCrawler\Tests\Parser\Item\Types;
use yCrawler\Parser\Item\Types;
use yCrawler\Tests\Testcase;

class XPathTypeTest extends Type
{
    const TESTED_CLASS = 'yCrawler\Parser\Item\Types\XPathType';

    const EXAMPLE_PATTERN_INPUT = '/foo/';
    const EXAMPLE_PATTERN_OUTPUT = '/foo/';
    const EXAMPLE_RESULT = 'foo';
}
