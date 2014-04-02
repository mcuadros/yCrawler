<?php

namespace yCrawler\Tests\Crawler\Runner;

use yCrawler\Crawler\Request;
use yCrawler\Crawler\Runner\ForkedRunner;
use Mockery as m;
use yCrawler\Crawler;
use yCrawler\Document;
use yCrawler\Mocks\DocumentMock;
use yCrawler\Parser;

class ForkedRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsFull()
    {
        $request = m::mock('yCrawler\Crawler\Request');

        $pool = m::mock('yCrawler\Crawler\Runner\Pool');
        $pool->shouldReceive('hasWaiting')->andReturn(false);

        $runner = new ForkedRunner($request, $pool);

        $this->assertTrue($runner->isFull());
    }

    public function testIsNotFull()
    {
        $request = m::mock('yCrawler\Crawler\Request');
        $pool = m::mock('yCrawler\Crawler\Runner\Pool');
        $pool->shouldReceive('hasWaiting')->andReturn(true);

        $runner = new ForkedRunner($request, $pool);
        $this->assertFalse($runner->isFull());
    }

    public function testRunnerRunsSuccessfully()
    {
        $request = m::mock('yCrawler\Crawler\Request');
        $request->shouldReceive('execute');
        $request->shouldReceive('setUrl');
        $request->shouldReceive('getResponse');
        $pool = m::mock('yCrawler\Crawler\Runner\Pool');

        $runner = new ForkedRunner($request, $pool);
    }
}
