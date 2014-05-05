<?php

namespace yCrawler\Crawler\Queue;

use yCrawler\Crawler\Queue;
use yCrawler\Crawler\Queue\Exceptions;
use yCrawler\Document;
use SplPriorityQueue;

class SimpleQueue implements Queue
{
    private $queue;

    public function __construct()
    {
        $this->clear();
    }

    public function add(Document $document, $priority = self::PRIORITY_NORMAL)
    {
        if ($this->has($document)) {
            throw new Exceptions\DuplicateDocument();
        }

        $this->queue->insert($document, $priority);
    }

    public function addMultiple(array $documents)
    {
        foreach ($documents as $document) {
            if (!is_array($document)) {
                $document = [$document];
            }

            if (! $document[0] instanceof Document) {
                continue;
            }

            if (!isset($document[1])) {
                $document[1] = self::PRIORITY_NORMAL;
            }

            $this->add($document[0], $document[1]);
        }

    }

    public function has(Document $document)
    {
        $clonedQueue = clone $this->queue;
        foreach ($clonedQueue as $key => $stored) {
            if ($stored->getURL() == $document->getURL()) {
                return true;
            }
        }

        return false;
    }

    public function clear()
    {
        $this->queue = new SplPriorityQueue();
    }

    public function get()
    {
        if (!$this->queue->valid()) {
            return false;
        }
        return $this->queue->extract();
    }

    public function delete(Document $document)
    {
        $clonedQueue = clone $this->queue;
        $clonedQueue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);

        $this->clear();

        foreach ($clonedQueue as $key => $stored) {
            if ($stored['data']->getURL() != $document->getURL()) {
                $this->add($stored['data'], $stored['priority']);
            }
        }
    }

    public function count()
    {
        return count($this->queue);
    }
}
