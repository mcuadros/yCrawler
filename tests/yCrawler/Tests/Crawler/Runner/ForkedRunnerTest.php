<?php

namespace yCrawler\Tests\Crawler\Runner;

use yCrawler\Crawler\Runner\ForkedRunner;
use Mockery as m;

class ForkedRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsFull()
    {
        $request = m::mock('yCrawler\Crawler\Request');
        $document = m::mock('yCrawler\Document');

        $runner = new ForkedRunner($request);
        $runner->addDocument($document);

        $this->assertTrue($runner->isFull());
    }

    public function testIsNotFull()
    {
        $request = m::mock('yCrawler\Crawler\Request');

        $runner = new ForkedRunner($request);
        $this->assertFalse($runner->isFull());
    }
}
