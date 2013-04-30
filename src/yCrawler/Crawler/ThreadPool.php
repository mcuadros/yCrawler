<?php
namespace yCrawler\Crawler;
use Aza\Components\Thread\ThreadPool as AzaThreadPool;
use yCrawler\Queue;

class ThreadPool extends AzaThreadPool {
    public $debug = false;
    private $queue;

    public function __construct($threadName, $maxThreads = null,
        $pName = null, $debug = false, $name = 'base')
    {
        $debug && $this->debug = true;

        $this->id       = ++self::$allPoolsCount;
        $this->poolName = $name;
        $this->tName    = $threadName;

        !Thread::$useForks && $this->maxThreads = 1;
        isset($maxThreads) && $this->setMaxThreads($maxThreads);
        isset($pName)      && $this->pName = $pName;

        $this->debug(
            "Pool of '$threadName' threads created."
        );

       // $this->createAllThreads();
    }

    public function setQueue(Queue $queue) {
        $this->queue = $queue;
    }

    public function start() {
        $this->createAllThreads();
        
        do {
            while ( $doc = $this->queue->get() && $this->hasWaiting() ) {
                var_dump($doc); exit();
                if (!$threadId = $this->run($doc)) {
                    throw new Exception('Pool slots error');
                }
            }
    
            if ($results = $this->wait($failed)) {
                foreach ($results as $threadId => $doc) {
                    echo "Done: " . $doc->getUrl() . PHP_EOL;
                }
            }
   
            if ($failed) {
                // Error handling here
                // processing is not successful if thread dies
                // when worked or working timeout exceeded
                foreach ($failed as $threadId) {
                    $jobs[] = $started[$threadId];
                    echo "error: {$started[$threadId]} (thread $threadId)", PHP_EOL;
                    unset($started[$threadId]);
                    $left++;
                }
            }
        } while (1);
        $this->cleanup();
    }
}
