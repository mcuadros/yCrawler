<?php
namespace yCrawler\Crawler;
use Aza\Components\Thread\Thread as AzaThread;

class Thread extends AzaThread {
    protected function process() {
        echo '[' . getmypid() . '] Running job: ' . $this->getParam(0) . PHP_EOL;
        sleep(3);

        return $this->getParam(0);
    }
}
