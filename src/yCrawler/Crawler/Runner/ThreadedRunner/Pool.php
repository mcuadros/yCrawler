<?php
namespace yCrawler\Crawler\Runner\ThreadedRunner;
use yCrawler\Crawler\Runner\ThreadedRunner\Exceptions;
use yCrawler\Crawler\Runner\ThreadedRunner\Work;
use yCrawler\Crawler\Runner\ThreadedRunner\Worker;

class Pool
{
    const DEFAULT_POOL_SIZE = 10;
    private $poolSize = self::DEFAULT_POOL_SIZE;

    public $workers;
    public $status;

    public function setPoolSize($size)
    {
        $this->poolSize = $size;
    }

    public function getPoolSize()
    {
        return $this->poolSize;
    }

    public function submitWork(Work $work) {
        if (!$this->poolSizeReached()) {
            $worker = $this->launchWorker($work);
        } else {
            $worker = $this->getRandomWorker();
        }

        if ($worker->stack($work)) {
            echo "HOLA";
            return $work;
        } 
    
        throw new Exceptions\FailedToStack();
    }

    private function poolSizeReached()
    {
        return count($this->workers) == $this->poolSize;
    }

    private function createNewWorkerId()
    {
        return count($this->workers);
    }

    private function launchWorker()
    {
        $id = $this->createNewWorkerId();

        $worker = new Worker(sprintf("Worker [%d]", $id));
        $worker->start(PTHREADS_INHERIT_ALL);

        $this->workers[$id] = $worker;

        return $worker;
    }

    private function getRandomWorker()
    {
        return $this->workers[array_rand($this->workers)];
    }

    public function shutdownWorkers() {
        $status = [];

        foreach($this->workers as $worker) {
            $status[$worker->getThreadId()] = $worker->shutdown();
        }
        
        return $status;
    }
}