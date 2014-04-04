<?php

namespace yCrawler;

use yCrawler\Config;
use yCrawler\Crawler\Queue\SimpleQueue;
use yCrawler\Crawler\Request;
use yCrawler\Crawler\Runner\BasicRunner;
use yCrawler\Crawler\Runner\ForkedRunner;
use yCrawler\Crawler\Runner\ForkedRunner\Pool;
use yCrawler\Document\Generator;

class CrawlerFactory
{
    public static function createSimple(array $configurations)
    {
        $queue = new SimpleQueue();
        $runner = new BasicRunner(new Request());

        self::populate($queue, $configurations);

        return new Crawler($queue, $runner);
    }

    public static function createThreaded(array $configurations)
    {
        $queue = new SimpleQueue();

        self::populate($queue, $configurations);

        $threads = Pool::DEFAULT_THREADS;
        if ($queue->count() <= Pool::DEFAULT_THREADS) {
            $threads = $queue->count() - 1;
        }

        $runner = new ForkedRunner(new Request(), new Pool($threads));

        return new Crawler($queue, $runner);
    }

    protected static function populate($queue, $configurations)
    {
        $documents = [];
        $generator = new Generator();
        foreach ($configurations as $config) {
            if (!$config instanceof Config) {
                throw new \InvalidArgumentException('Only instances of yCrawler\Config allowed');
            }

            if ($file = $config->getUrlsFile()) {
                $documents = $generator->getDocuments($file, $config->getParser());
            }

            if ($root = $config->getRootUrl()) {
                $documents[] = new Document($root, $config->getParser());
            }

            $queue->addMultiple($documents);
        }
    }
}
