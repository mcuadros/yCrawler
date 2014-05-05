<?php

namespace yCrawler\Tests\Cache\Driver;

use yCrawler\Cache\Driver\File;

class FileTest extends \PHPUnit_Framework_TestCase
{
    private $cache;

    protected function setUp()
    {
        $this->markTestSkipped();
        $this->cache = new File();
    }

    protected function tearDown()
    {
//        $this->cache->clear();
    }

    public function testSet()
    {
        $this->markTestSkipped();
        $data = 1000;
        $this->assertFalse($this->cache->get('testSet'));

        $this->cache->set('testSet', $data);
        $this->assertEquals($data, $this->cache->get('testSet'));
    }

    public function testInfo()
    {
        $this->markTestSkipped();
        $data = 1000;
        $this->cache->set('testInfo', $data);

        $info = $this->cache->info('testInfo');
        $this->assertEquals(1000, $info['data']);
        $this->assertEquals(0, $info['ttl']);
    }

    public function testDelete()
    {
        $this->markTestSkipped();
        $data = 1000;
        $this->cache->set('testDelete', $data);
        $this->cache->delete('testDelete');

        $this->assertFalse($this->cache->get('testDelete'));
    }

    public function testClear()
    {
        $this->markTestSkipped();
        $data = 1000;
        $this->cache->set('testClear', $data);
        $this->cache->clear();

        $this->assertFalse($this->cache->get('testClear'));
    }
}
