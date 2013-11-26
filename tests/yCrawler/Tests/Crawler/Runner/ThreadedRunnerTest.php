<?php
namespace yCrawler\Tests\Crawler\Runner;
use yCrawler\Crawler\Runner\ThreadedRunner;
use yCrawler\Crawler\Runner\ThreadedRunner\Work;

class ThreadedRunnerTest extends TestCase
{
    protected function createRunner()
    {
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $runner = new ThreadedRunner();

        $works = array();
        for ($target = 0; $target < 100; $target++) {
            echo "Added \n";
           // $objects[] = $object = new Example();
            $object = null;
            $works[]=$runner->submitWork(new Work($object));
        }


        $printed = 0;

        while(1) {
            $result = ['terminated' => 0, 'running' => 0, 'waiting' => 0, 'finished' => 0];
            foreach ($works as $key => $value) {
                if ($value->dataWaiting()) {
                    $printed++;
                    var_dump($value->getObject(), $value->getLocal());
                }
                
                if ($value->isFinished()) $result['finished']++;
                if ($value->isTerminated()) $result['terminated']++;

                if ($value->isWaiting()) $result['waiting']++;
                if ($value->isRunning()) $result['running']++;
            }

            print_r($result);

            if ($result['finished'] + $result['terminated'] == 100) break;
        }

        var_dump($printed);
        exit();
    }
}

