<?php
namespace yCrawler\Crawler\Runner\ThreadedRunner;
use Worker as PThreadWorker;

class Worker extends PThreadWorker {
    public function run()
    {
        printf("Creating worker (%lu) \n", $this->getThreadId());
    }
}