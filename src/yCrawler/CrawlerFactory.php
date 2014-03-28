<?php

namespace yCrawler;

use yCrawler\Config;
use yCrawler\Crawler\Queue\SimpleQueue;
use yCrawler\Crawler\Request;
use yCrawler\Crawler\Runner\BasicRunner;
use yCrawler\Document\Generator;

class CrawlerFactory 
{
    public static function createSimple(array $configurations)
    {
        $documents = [];
        $generator = new Generator();
        $queue = new SimpleQueue();
        $runner = new BasicRunner(new Request());

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

        return new Crawler($queue, $runner);
    }
}
