<?php

namespace yCrawler\Crawler\Runner\ForkedRunner;

use Aza\Components\Thread\Thread;

class Fork extends Thread
{
    public function process()
    {
        $work = $this->getParam(0);

        if (!$work instanceof Work) {
            throw new Exceptions\NonRecievedWork;
        }

        $work->run();

        return $work;
    }
}
