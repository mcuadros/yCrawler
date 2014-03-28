<?php

namespace yCrawler\Tests;

use PHPUnit_Framework_TestCase;
use yCrawler\Mocks;
use Mockery as m;
use yCrawler\Parser;
use yCrawler\Parser\Rule;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected function createParserMock()
    {
        $parser = new Parser('test');
        $parser->addLinkFollowRule(new Rule\XPath('//a'), true);
        $parser->addVerifyRule(new Rule\XPath('//a'), true);

        $parser->addValueRule(new Rule\XPath('//no-exists-tag'), 'no-exists');
        $parser->addValueRule(new Rule\XPath('//pre'), 'pre');

        return $parser;
    }

    protected function createDocumentMock()
    {
        $document = m::mock('yCrawler\Document');

        return $document;
    }

    protected function createXPathMock()
    {
        $document = m::mock('DOMXPath');

        return $document;
    }
}
