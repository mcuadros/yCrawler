<?php

namespace yCrawler\Mocks;

use yCrawler\Parser;

class ParserMock extends Parser
{
    public function initialize()
    {
        $this->createLinkFollowItem('//a');
        $this->createVerifyItem('//a');

        $this->createValueItem('no-exists', '//no-exists-tag');
        $this->createValueItem('pre', '//pre');
    }

    public function matchUrl($url)
    {
        return true;
    }
}
