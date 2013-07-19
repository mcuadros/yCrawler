<?php
namespace yCrawler\Queue;

use yCrawler\Document;

class History
{
    private $storage = array();

    public function hasDocument(Document $document)
    {
        $url = $document->getUrl();
        return $this->hasURL($url);
    }

    public function hasURL($url)
    {
        return isset($this->storage[$url]);
    }

    public function getDocumentHits(Document $document)
    {
        $url = $document->getUrl();
        return $this->getURLHits($url);
    }

    public function getURLHits($url)
    {
        if (!$this->hasURL($url)) {
            return false;
        }

        return $this->storage[$url];
    }

    public function addDocument(Document $document)
    {
        $url = $document->getUrl();
        $this->addURL($url);
    }

    public function addURL($url)
    {
        if (!$this->hasURL($url)) {
            $this->storage[$url] = 1;  
        } else {
            $this->storage[$url]++;
        }
    }
}