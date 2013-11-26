<?php
namespace yCrawler\Tests\Crawler\Runner;
use yCrawler\Crawler\Runner\ForkedRunner;

class ForkedRunnerTest extends TestCase
{ 
    protected function createRunner()
    {
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        return new ForkedRunner();
    }

    protected function getPoolSize()
    {
        return 10;
    }
}

