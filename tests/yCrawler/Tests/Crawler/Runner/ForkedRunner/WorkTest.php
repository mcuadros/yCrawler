<?php
namespace yCrawler\Tests\Crawler\Runner\ForkedRunner;
use yCrawler\Tests\TestCase;
use yCrawler\Crawler\Runner\ForkedRunner\Work;
use yCrawler\Crawler\Runner\ForkedRunner\Pool;
use yCrawler\Document;
use Exception;

class WorkTest extends TestCase
{
    /**
     * @outputBuffering disabled
     */
    public function testForkerd()
    {   
        $this->expectOutputString(''); // tell PHPUnit to expect '' as output

        $document = new Document('http://httpbin.org/', $this->createParserMock());
        $work = new Work($document);

        $pool = new Pool('yCrawler\Crawler\Runner\ForkedRunner\Fork', 10);
        $pool->run($work);

        
        //$this->assertNull($work->getException());

        $loops = 0;
        while(1){ 
            try {
                if ($loops++ > 5) break;

                if ($results = $pool->wait($failed)) {
                    foreach ($results as $threadId => $result) {
                        $resultDocument = $result;
                        //echo "result: $result (thread $threadId)", PHP_EOL;
                    }
                }

                if ($failed) 
                {
                    var_dump($failed);
                }

                echo "Loop";
            } catch (Exception $e) {
                echo $e->getMessage();
                break;
            }
        }
        $this->assertSame($resultDocument->getDocument(), $resultDocument->isParsed());

    }
}