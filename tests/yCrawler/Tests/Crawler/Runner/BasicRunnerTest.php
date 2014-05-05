<?php

namespace yCrawler\Tests\Crawler\Runner;

use GuzzleHttp\Client;
use yCrawler\Crawler\Runner\BasicRunner;

class BasicRunnerTest extends TestCase
{ 
    protected function createRunner(Client $client)
    {
        return new BasicRunner($client);
    }

    protected function getPoolSize()
    {
        return 1;
    }
}
