<?php

namespace yCrawler\Document;

use yCrawler\Misc\URL;
use yCrawler\Parser;

use ArrayIterator;
use IteratorAggregate;
use Countable;

class LinksStorage implements IteratorAggregate, Countable
{
    private $originURL;
    private $parser;
    private $links = [];

    public function __construct($originURL, Parser $parser)
    {
        $this->parser = $parser;
        $this->originURL = $originURL;
    }

    public function all()
    {
        return $this->links;
    }

    public function add($uri)
    {
        $url = $this->absolutizeURI($uri);
        $this->links[$url][] = true;
        if ($this->isSuitableURL($url)) {
            $this->links[$url][] = true;
        }
    }

    public function has($uri)
    {
        $url = $this->absolutizeURI($uri);

        return isset($this->links[$url]);
    }

    public function remove($uri)
    {
        $url = $this->absolutizeURI($uri);
        unset($this->links[$url]);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->links);
    }

    public function count()
    {
        return count($this->links);
    }

    private function isSuitableURL($url)
    {
        if (!$url) {
            return false;
        }

        return $this->parser->matchURL($url);
    }

    private function absolutizeURI($uri)
    {
        return URL::absolutize($uri, $this->originURL);
    }
}
