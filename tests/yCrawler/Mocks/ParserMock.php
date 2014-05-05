<?php

namespace yCrawler\Mocks;

use yCrawler\Parser;
use yCrawler\Parser\Rule;

class ParserMock extends Parser
{
    public function __construct()
    {
        $this->addLinkFollowRule(new Rule\XPath('//a'), true);
        $this->addVerifyRule(new Rule\XPath('//a'), true);

        $this->addValueRule(new Rule\XPath('//no-exists-tag'), 'no-exists');
        $this->addValueRule(new Rule\XPath('//pre'), 'pre');
    }

    public function matchUrl($url)
    {
        return true;
    }
}
