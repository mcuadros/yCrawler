<?php

namespace yCrawler\Tests\Crawler\Runner;

use yCrawler\Crawler\Request;
use yCrawler\Crawler\Runner\BasicRunner;

class BasicRunnerTest extends TestCase
{ 
    protected function createRunner(Request $request)
    {
        return new BasicRunner($request);
    }

    protected function getPoolSize()
    {
        return 1;
    }
}

