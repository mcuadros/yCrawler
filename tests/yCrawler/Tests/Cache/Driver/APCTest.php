<?php
namespace yCrawler\Tests\Cache\Driver;
use yCrawler\Cache\Driver\APC;
use yCrawler\Crawler;


class APCTest extends \PHPUnit_Framework_TestCase { 
    private $cache;

    protected function setUp() {
        $this->cache = new APC(new Crawler());

        if (version_compare(phpversion(), '5.5.0', '>=')) {
            $this->markTestSkipped('Skiped due to APCu problems.');
        }
    }

    protected function tearDown() {
        $this->cache->clear();
    }

    public function testSet() {
        $data = 1000;
        $this->assertFalse($this->cache->get('testSet'));

        $this->assertTrue($this->cache->set('testSet', $data));
        $this->assertEquals($data, $this->cache->get('testSet'));
    }

    public function testInfo() {
        $data = 1000;
        $this->cache->set('testSet', $data);

        $info = $this->cache->info('testSet');
        $this->assertEquals(1000, $info['data']);
        $this->assertEquals(0, $info['ttl']);
    }

    public function testDelete() {
        $data = 1000;
        $this->cache->set('testSet', $data);
        $this->cache->delete('testSet');

        $this->assertFalse($this->cache->delete('testSet'));
    }

    public function testClear() {
        $data = 1000;
        $this->cache->set('testSet', $data);
        $this->cache->clear();

        $this->assertFalse($this->cache->delete('testSet'));
    }
}

