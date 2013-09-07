<?php
namespace yCrawler\Crawler\Runner;
use yCrawler\Crawler\Runner;
use yCrawler\Document;
use yCrawler\Crawler\Runner\ThreadedRunner\Exceptions;
use yCrawler\Crawler\Runner\ThreadedRunner\Work;
use yCrawler\Crawler\Runner\ThreadedRunner\Worker;
use Exception;

class ThreadedRunner extends Runner
{
    const DEFAULT_POOL_SIZE = 10;
    private $poolSize = self::DEFAULT_POOL_SIZE;

    public $workers;
    public $status;

    public function parseDocument(Document $document)
    {
        try {
            $document->parse();
            $this->onDone($document);
        } catch (Exception $exception) {
            $this->onFaild($document, $exception);
        }
    }

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
        $worker->start();

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