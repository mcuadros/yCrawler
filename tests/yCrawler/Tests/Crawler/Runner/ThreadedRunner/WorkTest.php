<?php
namespace yCrawler\Tests;
use yCrawler\Tests\TestCase;
use yCrawler\Crawler\Runner\ThreadedRunner\Work;
use yCrawler\Crawler\Runner\ThreadedRunner\Pool;
use yCrawler\Document;

class WorkTest extends TestCase
{
    public function testCreateItem()
    {   
        $document = new Document('foo', $this->createParserMock());

        $work = new Work($document);
        $pool = new Pool();
        $pool->submitWork($work);


        echo "hola";

        //$this->assertInstanceOf('yCrawler\Parser\Item', $group->createItem());
    }
}