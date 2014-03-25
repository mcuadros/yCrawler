<?php

namespace yCrawler\Tests;

use yCrawler\Parser;
use yCrawler\Parser\Item;

class ParserMock extends Parser
{
    public function initialize()
    {
        $item = new Item();
        $item->setPattern('//h2');

        $this->addValueItem('headers', $item);
    }

} 