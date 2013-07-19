<?php
namespace yCrawler;

use yCrawler\Queue\History;
use yCrawler\Queue\Exceptions;
use SplPriorityQueue;

class Queue
{
    const PRTY_NONE = 10;
    const PRTY_LOW = 20;
    const PRTY_NORMAL = 30;
    const PRTY_HIGH = 40;
    const PRTY_URGENT = 50;
    const PRTY_INMEDIATE = 60;

    private $queue;
    private $history = array();

    public function __construct()
    {
        $this->restart();
    }

    public function restart()
    {
        $this->queue = new SplPriorityQueue();
        $this->history = new History();

    }

    public function add(Document $document, $priority = self::PRTY_NORMAL)
    {
        if ($this->hasDocumentInHisotry($document)) {
            throw new Exceptions\DuplicateDocument();
        }

        $this->insertDocumentToQueue($document, $priority);
        $this->addDocumentToHistory($document);
    }

    public function retry(Document $document, $priority = self::PRTY_NORMAL)
    {
        if (!$this->hasDocumentInHisotry($document)) {
            throw new Exceptions\DocumentNotFound();
        }
        
        $this->insertDocumentToQueue($document, $priority);
    }

    public function get()
    {
        if (!$this->queue->valid()) return false;
        return $this->queue->extract();
    }

    private function hasDocumentInHisotry(Document $document)
    {
        return $this->history->hasDocument($document);
    }

    private function addDocumentToHistory(Document $document)
    {
        $this->history->addDocument($document);
    }

    private function insertDocumentToQueue(Document $document, $priority)
    {
        $this->queue->insert($document, $priority);
    }
}
