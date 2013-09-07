<?php
namespace yCrawler\Crawler\Runner\ThreadedRunner;
use Stackable;

class Work extends Stackable {
    private $finished;
    private $object;
    private $waiting;

    public function __construct($data) {
        $this->local = $data;
    }

    public function run() {
        printf('Running on %s pid(%s)'  . PHP_EOL, $this->worker->getThreadId(), getmypid());

        $this->worker->addData([
            'date' => date('Y-m-d H:i:s'),
            'worker' => $this->worker->getThreadId()
        ]);

        $object = $this->local;
       // $object->add();
        
        $this->object = $object;
        //sleep(1);

        $this->finished = true;
        $this->waiting = true;

    }   

    public function getObject()
    {
        return $this->object;
    }

    public function getLocal()
    {
        return $this->local;
    }

    public function isFinished()
    {
        return $this->finished;
    }

    public function dataWaiting()
    {
        $status = $this->waiting;
        $this->waiting = false;

        return $status;
    }
}