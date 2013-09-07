<?php
namespace yCrawler\Crawler;
use yCrawler\Document;

interface Queue
{
    const PRIORITY_NONE = 10;
    const PRIORITY_LOW = 20;
    const PRIORITY_NORMAL = 30;
    const PRIORITY_HIGH = 40;
    const PRIORITY_URGENT = 50;
    const PRIORITY_INMEDIATE = 60;

    public function add(Document $document, $priority = self::PRIORITY_NORMAL);
    
    public function has(Document $document);
    
    public function delete(Document $document);
    
    public function get();

    public function clear();

    public function count();
}
