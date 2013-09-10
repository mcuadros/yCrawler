<?php
namespace yCrawler\Crawler\Runner;
use yCrawler\Crawler\Runner;
use yCrawler\Crawler\Runner\ThreadedRunner\Pool;
use yCrawler\Crawler\Runner\ThreadedRunner\Work;
use yCrawler\Document;
use Exception;

class ForkedRunner extends Runner
{
    private $pool;
    private $works;

    public function __construct()
    {
        $this->pool = new Pool();
    }

    public function parseDocument(Document $document)
    {
        $work = $this->createWork($document);
        $this->pool->submitWork($work);

    }

    private function createWork(Document $document)
    {
        $this->works[] = $work = new Work($document);
        return $work;
    }

}


/*
        $loops = 0;
        while(1){ 
            try {
                if ($loops++ > 5) break;

                if ($results = $pool->wait($failed)) {
                    foreach ($results as $threadId => $result) {
                        $resultDocument = $result;
                        //echo "result: $result (thread $threadId)", PHP_EOL;
                    }
                }

                if ($failed) 
                {
                    var_dump($failed);
                }

                echo "Loop";
            } catch (Exception $e) {
                echo $e->getMessage();
                break;
            }
        }
        */