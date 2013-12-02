<?php
require __DIR__ . '/../vendor/autoload.php';

use yCrawler\Crawler;
use yCrawler\Parser;
use yCrawler\Parser\Item;
use yCrawler\Crawler\Queue\SimpleQueue;
use yCrawler\Crawler\Runner\BasicRunner;

class BasicParser extends Parser
{
    public function initialize()
    {
        $this->setStartupURL('http://httpbin.org/');

        $item = new Item();
        $item->setPattern('//h2');

        $this->addValueItem('headers', $item);
    }
}

$queue = new SimpleQueue(); 
$runner = new BasicRunner();

$crawler = new Crawler($queue, $runner);
$crawler->addParser(new BasicParser());
$crawler->onParse(function($document) {
    var_dump($document->getValues());
});
$crawler->run();