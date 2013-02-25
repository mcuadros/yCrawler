<?php
use Aza\Components\Cli\Thread\ThreadPool as AzaThreadPool;

class ThreadPool extends AzaThreadPool {
    protected function process() {
        echo '[' . getmypid() . '] Running job: ' . $this->getParam(0) . PHP_EOL;
        sleep(3);

        return $this->getParam(0);
    }
}
