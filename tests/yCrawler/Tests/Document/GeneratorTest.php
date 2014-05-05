<?php

namespace yCrawler\Tests\Document;

use yCrawler\Document\Generator;
use Mockery as m;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateDocuments()
    {
        $parser = m::mock('yCrawler\Parser');
        $generator = new Generator();
        $docs = $generator->getDocuments(__DIR__ . '/../../../Resources/urls.csv', $parser);
        $this->assertCount(4, $docs);

        foreach ($docs as $doc) {
            $this->assertInstanceOf('yCrawler\Document', $doc);
            $this->assertSame($parser, $doc->getParser());
        }
    }

    public function testAddPatterns()
    {
        $parser = m::mock('yCrawler\Parser');
        $generator = new Generator();
        $generator->setPatterns(['~^https?://httpbin\d\..+~m']);
        $docs = $generator->getDocuments(__DIR__ . '/../../../Resources/urls.csv', $parser);
        $this->assertCount(3, $docs);
    }
}