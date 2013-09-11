<?php
namespace yCrawler\Tests\Crawler\Runner;
use yCrawler\Crawler\Runner\ForkedRunner;

class ForkedRunnerTest extends TestCase
{ 
    protected function createRunner()
    {
        return new ForkedRunner();
    }

    protected function getPoolSize()
    {
        return 10;
    }
}

