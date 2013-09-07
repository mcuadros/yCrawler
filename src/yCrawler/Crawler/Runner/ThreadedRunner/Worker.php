<?php
namespace yCrawler\Crawler\Runner\ThreadedRunner;
use Worker as PThreadWorker;

class Worker extends PThreadWorker {

    public function __construct($name) {
        $this->name = $name;
        $this->data = array();
        $this->setup = false;
        $this->attempts = 0;
    }
    
    public function run()
    {
        printf("Creating worker (%lu) \n", $this->getThreadId());
    }

    public function setData($data)      
    { 
        $this->data = $data; 
    }
    
    public function addData($data)      
    { 
        $this->data = array_merge($this->data, array($data)); 
    }
    
    public function getData()
    { 
        return $this->data; 
    }
}