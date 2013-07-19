<?php
namespace yCrawler\Tests;
use yCrawler\Queue;
use yCrawler\Document;

class QueueTest extends  \PHPUnit_Framework_TestCase
{
    public function testInsert()
    {
        $queue = new Queue;
        $doc = new Document('http://www.test.com');

        $queue->add($doc);
    }

    /**
     * @expectedException yCrawler\Queue\Exceptions\DuplicateDocument
     */
    public function testDoubleInsert()
    {
        $queue = new Queue;
        $doc = new Document('http://www.test.com');

        $queue->add($doc);
        $queue->add($doc);
    }

    public function testGet()
    {
        $queue = new Queue;
        $doc = new Document('http://www.test.com');

        $queue->add($doc);
        $this->assertSame($doc, $queue->get($doc));
        $this->assertFalse($queue->get($doc));
    }

    public function testRetry()
    {
        $queue = new Queue;
        $doc = new Document('http://www.test.com');

        $queue->add($doc);
        $queue->retry($doc);
    }

    /**
     * @expectedException yCrawler\Queue\Exceptions\DocumentNotFound
     */
    public function testRetryWithoutAdd()
    {
        $queue = new Queue;
        $doc = new Document('http://www.test.com');

        $queue->retry($doc);
    }

    public function testDefaultPriority()
    {
        $queue = new Queue;
        $docA = new Document('http://www.testA.com');
        $docB = new Document('http://www.testB.com');

        $queue->add($docA);
        $queue->add($docB);
        $this->assertSame($docA, $queue->get());
    }

    public function testDefaultPriorityLow()
    {
        $queue = new Queue;
        $docA = new Document('http://www.testA.com');
        $docB = new Document('http://www.testB.com');

        $queue->add($docA);
        $queue->add($docB, Queue::PRTY_LOW);
        $this->assertSame($docA, $queue->get());
    }

    public function testDefaultPriorityHigh()
    {
        $queue = new Queue;
        $docA = new Document('http://www.testA.com');
        $docB = new Document('http://www.testB.com');

        $queue->add($docA);
        $queue->add($docB, Queue::PRTY_HIGH);
        $this->assertSame($docB, $queue->get());
    }
}
