<?php

namespace yCrawler;

use GuzzleHttp\Client;
use yCrawler\Crawler\Queue\SimpleQueue;
use yCrawler\Crawler\Runner\BasicRunner;
use yCrawler\Crawler\Runner\ForkedRunner;
use yCrawler\Crawler\Runner\ForkedRunner\Pool;
use yCrawler\Crawler\Web;
use yCrawler\Document\Generator;
use yCrawler\Document;

class CrawlerFactory
{
    public function createSimple(array $configurations)
    {
        $webs = [];
        foreach ($configurations as $config) {
            if (!$config instanceof Config) {
                throw new \InvalidArgumentException('Only instances of yCrawler\Config allowed');
            }
            $queue = new SimpleQueue();

            $this->populate($queue, $config);

            $runner = new BasicRunner(new Client(['connect_timeout' => $config->getRequestTimeOut()]));
            $web = new Web();
            $web->queue = $queue;
            $web->runner = $runner;
            $web->parallelRequests = $config->getParallelRequests();
            $web->waitTime = $config->getWaitTimeBetweenRequests();
            $webs[] = $web;
        }

        return new Crawler($webs);
    }

    public function createForked(array $configurations)
    {
        $webs = [];
        $pool = new Pool();
        foreach ($configurations as $config) {
            if (!$config instanceof Config) {
                throw new \InvalidArgumentException('Only instances of yCrawler\Config allowed');
            }
            $queue = new SimpleQueue();

            $this->populate($queue, $config);

            $runner = new ForkedRunner(new Client(['connect_timeout' => $config->getRequestTimeOut()]), $pool);
            $webs[] = $this->createWeb($queue, $runner, $config);
        }

        return new Crawler($webs);
    }

    protected function createWeb($queue, $runner, $config)
    {
        $web = new Web();
        $web->queue = $queue;
        $web->runner = $runner;
        $web->parallelRequests = $config->getParallelRequests();
        $web->waitTime = $config->getWaitTimeBetweenRequests();

        return $web;
    }

    protected function populate($queue, $config)
    {
        $documents = [];
        $generator = new Generator();

        if ($file = $config->getUrlsFile()) {
            $documents = $generator->getDocuments($file, $config->getParser());
        }

        if ($root = $config->getRootUrl()) {
            $documents[] = new Document($root, $config->getParser());
        }

        $queue->addMultiple($documents);
    }
}
