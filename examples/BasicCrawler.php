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

        $item = new Item();
        $item->setPattern('//h2');

        $this->addValueItem('headers', $item);
    }
}

$parser = new BasicParser('name');
$parser->setOnParseCallback(function($document){});

$queue = new SimpleQueue();
$queue->add(new \yCrawler\Document('url', $parser));
$queue->add(new \yCrawler\Document('url', $parser));
$queue->add(new \yCrawler\Document('url', $parser));
$queue->add(new \yCrawler\Document('url', $parser));
$queue->add(new \yCrawler\Document('url', $parser));
$queue->add(new \yCrawler\Document('url', $parser));

$runner = new BasicRunner(new Crawler\Request());

$crawler = new Crawler($queue, $runner);
$crawler->onParse(function($document) {
    var_dump($document->getValues());
});
$crawler->run();