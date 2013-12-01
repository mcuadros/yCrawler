<?php

namespace yCrawler\Tests\Crawler\Runner;

use yCrawler\Crawler\Runner\BasicRunner;

class BasicRunnerTest extends TestCase
{ 
    protected function createRunner()
    {
        return new BasicRunner();
    }

    protected function getPoolSize()
    {
        return 1;
    }
}

