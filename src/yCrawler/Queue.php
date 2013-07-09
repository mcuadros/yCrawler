<?php
namespace yCrawler;

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
        $this->queue = new \SplPriorityQueue();
    }

    public function add(Document $document, $priority = self::PRTY_NORMAL)
    {
        $url = $document->getUrl();
        if ( $this->inHistory($url) ) return false;

        $this->queue->insert($document, $priority);
        $this->addHistory($url);

        return true;
    }

    public function retry(Document $document, $priority = self::PRTY_NORMAL)
    {
        $url = $document->getUrl();
        if ( !$this->inHistory($url) ) return false;

        $this->queue->insert($document, $priority);

        return true;
    }

    public function get()
    {
        if ( !$this->queue->valid() ) return false;
        return $this->queue->extract();
    }

    private function inHistory($url)
    {
        return isset($this->history[$url]);
    }

    private function addHistory($url)
    {
        $this->history[$url] = true;
    }
}
