<?php
namespace yCrawler\Crawler\Runner\ForkedRunner;
use Aza\Components\Thread\ThreadPool;

class Pool extends ThreadPool
{
    const THREAD_CLASS = 'yCrawler\Crawler\Runner\ForkedRunner\Fork';
    const DEFAULT_THREADS = 10;

    public function __construct(
        $maxThreads = self::DEFAULT_THREADS, 
        $pName = null, 
        $name = null, 
        $debug = false
    )
    {
        parent::__construct(self::THREAD_CLASS, $maxThreads, $pName, $name, $debug);
    }
}