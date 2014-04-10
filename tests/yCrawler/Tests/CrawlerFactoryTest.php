<?php

namespace yCrawler\Tests;

use Mockery as m;
use yCrawler\CrawlerFactory;
use yCrawler\Parser;

class CrawlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateForked()
    {
        $config = m::mock('yCrawler\Config');
        $config->shouldReceive('getUrlsFile')->andReturn([]);
        $config->shouldReceive('getRootUrl')->andReturn('root1');
        $config->shouldReceive('getParser')->andReturn(new Parser('test'));

        $this->assertInstanceOf('yCrawler\Crawler', CrawlerFactory::createForked([$config]));
    }

    public function testMultiUrlFromRootConfig()
    {
        $config = m::mock('yCrawler\Config');
        $config->shouldReceive('getUrlsFile')->andReturn([]);
        $config->shouldReceive('getRootUrl')->andReturn(['root1', 'root2']);
        $config->shouldReceive('getParser')->andReturn(new Parser('test'));

        $this->assertInstanceOf('yCrawler\Crawler', CrawlerFactory::createSimple([$config]));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidConfigClass()
    {
        $config = m::mock('StdClass');

        CrawlerFactory::createSimple([$config]);
    }
}
