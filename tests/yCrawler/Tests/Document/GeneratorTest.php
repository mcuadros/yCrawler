<?php

namespace yCrawler\Tests\Document;

use yCrawler\Document\Generator;
use Mockery as m;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateDocuments()
    {
        $parser = m::mock('yCrawler\Parser');
        $generator = new Generator($parser, __DIR__ . '/../../../Resources/urls.csv');
        $docs = $generator->getDocuments();
        $this->assertCount(4, $docs);

        foreach($docs as $doc) {
            $this->assertInstanceOf('yCrawler\Document', $doc);
            $this->assertSame($parser, $doc->getParser());
        }
    }

    public function testAddPatterns()
    {
        $parser = m::mock('yCrawler\Parser');
        $generator = new Generator($parser, __DIR__ . '/../../../Resources/urls.csv');
        $generator->setPatterns(['~^https?://httpbin\d\..+~m']);
        $docs = $generator->getDocuments();
        $this->assertCount(3, $docs);
    }
}