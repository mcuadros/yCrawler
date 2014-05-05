<?php

namespace yCrawler\Tests\Crawler\Runner;

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
        $client = m::mock('GuzzleHttp\Client');

        $pool = m::mock('yCrawler\Crawler\Runner\ForkedRunner\Pool');
        $pool->shouldReceive('hasWaiting')->andReturn(false);
        $pool->shouldReceive('cleanup')->andReturn(true);

        $runner = new ForkedRunner($client, $pool);

        $this->assertTrue($runner->isFull());
    }

    public function testIsNotFull()
    {
        $client = m::mock('GuzzleHttp\Client');
        $pool = m::mock('yCrawler\Crawler\Runner\ForkedRunner\Pool');
        $pool->shouldReceive('hasWaiting')->andReturn(true);
        $pool->shouldReceive('cleanup')->andReturn(true);

        $runner = new ForkedRunner($client, $pool);
        $this->assertFalse($runner->isFull());
    }

    public function testRunnerRunsSuccessfully()
    {
        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->andReturn(m::self());
        $client->shouldReceive('getBody');
        $pool = m::mock('yCrawler\Crawler\Runner\ForkedRunner\Pool');
        $pool->shouldReceive('hasWaiting')->andReturn(true);
        $pool->shouldReceive('cleanup')->andReturn(true);

        $runner = new ForkedRunner($client, $pool);
    }
}
