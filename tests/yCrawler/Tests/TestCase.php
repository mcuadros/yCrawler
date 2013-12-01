<?php

namespace yCrawler\Tests;

use PHPUnit_Framework_TestCase;
use yCrawler\Mocks;
use Mockery as m;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected function createParserMock()
    {
        $parser = new Mocks\ParserMock();

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
