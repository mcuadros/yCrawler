<?php
namespace yCrawler;
class Queue extends \SplPriorityQueue {
    const PRTY_NONE = 10;
    const PRTY_LOW = 20;
    const PRTY_NORMAL = 30;
    const PRTY_HIGH = 40;
    const PRTY_URGENT = 50;
    const PRTY_INMEDIATE = 60;

    private $history = array();

    public function insert($url, $priority = self::PRTY_NORMAL) {
        if ( $this->inHistory($url) ) return false;
        parent::insert($url, $priority);
        $this->addHistory($url);

        return true;
    }

    public function extract() {
        $url = parent::extract();
        $this->removeHistory($url);
        
        return $url;
    }

    private function inHistory($url) {
        return isset($this->history[$url]);
    }

    private function addHistory($url) {
        $this->history[$url] = true;
    }

    private function removeHistory($url) {
        unset($this->history[$url]);
    }
}