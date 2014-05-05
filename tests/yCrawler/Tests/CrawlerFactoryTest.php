<?php

namespace yCrawler\Tests;

use Mockery as m;
use yCrawler\CrawlerFactory;
use yCrawler\Parser;

class CrawlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateForked()
    {
        $cf = new CrawlerFactory();
        $config = $this->getConfigMock();
        $config->shouldReceive('getRootUrl')->andReturn('root');

        $this->assertInstanceOf('yCrawler\Crawler', $cf->createForked([$config]));
    }

    public function testMultiUrlFromRootConfig()
    {
        $cf = new CrawlerFactory();
        $config = $this->getConfigMock();
        $config->shouldReceive('getRootUrl')->andReturn(['root1', 'root2']);
        $this->assertInstanceOf('yCrawler\Crawler', $cf->createSimple([$config]));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidConfigClass()
    {
        $config = m::mock('StdClass');

        $cf = new CrawlerFactory();
        $cf->createSimple([$config]);
    }

    protected function getConfigMock()
    {
        $config = m::mock('yCrawler\Config');
        $config->shouldReceive('getUrlsFile')->andReturn([]);
        $config->shouldReceive('getParser')->andReturn(new Parser('test'));
        $config->shouldReceive('getRequestTimeOut')->andReturn(0);
        $config->shouldReceive('getParallelRequests')->andReturn(2);
        $config->shouldReceive('getWaitTimeBetweenRequests')->andReturn(0);

        return $config;
    }
}
