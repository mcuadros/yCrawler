<?php

namespace yCrawler\Crawler\Runner\ForkedRunner;

use Aza\Components\Thread\Thread;

class Fork extends Thread
{
    private $document;

    public function process()
    {
        $work = $this->getParam(0);
        if (!$work instanceOf Work) {
            throw new Exceptions\NonRecievedWork;
        }

        $work->run();

        return $work;
    }
}
