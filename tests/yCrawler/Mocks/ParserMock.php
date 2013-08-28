<?php
namespace yCrawler\Mocks;
use yCrawler\Parser;

class ParserMock extends Parser
{
    public function initialize()
    {
        $this->setStartupURL('http://httpbin.org/');

        $this->createLinkFollowItem('//a');
        $this->createVerifyItem('//a');

        $this->createValueItem('no-exists', '//no-exists-tag');
        $this->createValueItem('pre', '//pre');
    }
}
