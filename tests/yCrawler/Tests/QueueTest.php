<?php
namespace yCrawler\Tests;
use yCrawler\Queue;

class QueueTest extends  \PHPUnit_Framework_TestCase { 
    public function testInsert() {
        $queue = new Queue;
        $this->assertTrue($queue->insert('a'));
        $this->assertFalse($queue->insert('a'));
    }

    public function testExtract() {
        $queue = new Queue;
        $this->assertTrue($queue->insert('a'));
        $this->assertSame('a', $queue->extract());
        $this->assertTrue($queue->insert('a'));
    }

    public function testDefaultPriorityLow() {
        $queue = new Queue;
        $this->assertTrue($queue->insert('a'));
        $this->assertTrue($queue->insert('b', Queue::PRTY_LOW));
        $this->assertSame('a', $queue->extract());
    }

    public function testDefaultPriorityHigh() {
        $queue = new Queue;
        $this->assertTrue($queue->insert('a'));
        $this->assertTrue($queue->insert('b', Queue::PRTY_HIGH));
        $this->assertSame('b', $queue->extract());
    }
}

