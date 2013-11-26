<?php
namespace yCrawler\Tests\Crawler\Queue;
use yCrawler\Tests\TestCase;
use yCrawler\Document;
use yCrawler\Crawler\Queue\SimpleQueue;
use Mockery as m;

class SimpleQueueTest extends TestCase
{
    protected function createDocumentMock()
    {
        $document = parent::createDocumentMock();
        $document->shouldReceive('getURL')
            ->withNoArgs()
            ->andReturn(rand(0, 1000));

        return $document;
    }

    public function testAdd()
    {
        $document = $this->createDocumentMock();

        $queue = new SimpleQueue();
        $queue->add($document);
        $this->assertTrue($queue->has($document));
    }

    /**
     * @expectedException yCrawler\Crawler\Queue\Exceptions\DuplicateDocument
     */
    public function testAddDuplicate()
    {
        $document = $this->createDocumentMock();

        $queue = new SimpleQueue();
        $queue->add($document);
        $queue->add($document);
    }


    public function testHas()
    {
        $document = $this->createDocumentMock();

        $queue = new SimpleQueue();
        $this->assertFalse($queue->has($document));

        $queue->add($document);
        $this->assertTrue($queue->has($document));

        $document = $queue->get();
        $this->assertFalse($queue->has($document));
    }

    public function testDelete()
    {
        $documentA = $this->createDocumentMock();
        $documentB = $this->createDocumentMock();
        $documentC = $this->createDocumentMock();

        $queue = new SimpleQueue();
        $queue->add($documentA);
        $queue->add($documentB);
        $queue->add($documentC);

        $queue->delete($documentB);
        $this->assertFalse($queue->has($documentB));

        $this->assertSame($documentA, $queue->get());
        $this->assertSame($documentC, $queue->get());
    }

    public function testCount()
    {
        $queue = new SimpleQueue();

        $queue->add($this->createDocumentMock());
        $this->assertSame(1, $queue->count());

        $queue->add($this->createDocumentMock());
        $this->assertSame(2, $queue->count());

        $queue->get();
        $this->assertSame(1, $queue->count());
    }

    public function testGet()
    {
        $documentA = $this->createDocumentMock();
        $documentB = $this->createDocumentMock();
        $documentC = $this->createDocumentMock();

        $queue = new SimpleQueue();
        $queue->add($documentA, SimpleQueue::PRIORITY_NONE);
        $queue->add($documentB, SimpleQueue::PRIORITY_INMEDIATE);
        $queue->add($documentC, SimpleQueue::PRIORITY_HIGH);

        $this->assertSame($documentB, $queue->get());
        $this->assertSame($documentC, $queue->get());
        $this->assertSame($documentA, $queue->get());
    }
}

