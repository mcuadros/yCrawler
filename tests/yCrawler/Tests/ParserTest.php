<?php

namespace yCrawler\Tests;

use yCrawler\Document;
use yCrawler\Parser;
use yCrawler\Parser\Rule;
use yCrawler\Parser\Group;

class ParserTest extends TestCase
{
    const EXAMPLE_URL_A = 'http://foo.com/';
    const EXAMPLE_URL_B = 'http://bar.com/';
    const EXAMPLE_URL_MALFORMED = null;
    const EXAMPLE_PATTERN = '/foo/';
    const EXAMPLE_PATTERN_DOMAIN_BASED = '~^https?://foo\.com~';
    const EXAMPLE_PATTERN_MALFORMED = 'dsds';

    public function testConfigure()
    {
        $parser = $this->createParserMock();
        $this->assertFalse($parser->isInitialized());

        $parser->configure();
        $this->assertTrue($parser->isInitialized());
    }
    
    public function testSetURLPattern()
    {
        $parser = $this->createParserMock();
        $parser->setURLPattern(self::EXAMPLE_PATTERN);

        $this->assertCount(1, $parser->getURLPatterns());
        $this->assertSame([self::EXAMPLE_PATTERN], $parser->getURLPatterns());
    }

    /**
     * @expectedException yCrawler\Parser\Exceptions\InvalidURLPattern
     */
    public function testSetURLPatternInvalid()
    {
        $parser = $this->createParserMock();
        $parser->setURLPattern(self::EXAMPLE_PATTERN_MALFORMED);
    }

    public function testMatchURL()
    {
        $parser = $this->createParserMock();

        $parser->setURLPattern(self::EXAMPLE_PATTERN);
        $this->assertTrue($parser->matchURL(self::EXAMPLE_URL_A));
    }

    public function testAddLinkFollowRule()
    {
        $parser = $this->createParserMock();

        $rule = new Rule\XPath('');

        $parser->addLinkFollowRule($rule, false);
        $this->assertSame([
            [$rule, false]
        ], $parser->getFollowRules());

        $parser->addLinkFollowRule($rule, true);
        $this->assertSame([
            [$rule, false],
            [$rule, true]
        ], $parser->getFollowRules());

        $parser->clearFollowRules();
        $parser->addLinkFollowRule($rule, true);
        $this->assertSame([
            [$rule, true]
        ], $parser->getFollowRules());
    }

    public function testCreateLinkFollowRule()
    {
        $rule = new Rule\XPath('//a');

        $parser = $this->createParserMock();
        $parser->addLinkFollowRule($rule, false);
        $parser->addLinkFollowRule($rule, true);
        $parser->addLinkFollowRule($rule, true);

        $this->assertSame('//a', $rule->getPattern());

        $rules = $parser->getFollowRules();
        $this->assertInstanceOf('yCrawler\Parser\Rule', $rules[0][0]);
        $this->assertFalse($rules[0][1]);
        $this->assertTrue($rules[1][1]);
        $this->assertTrue($rules[2][1]);
    }

    public function testAddVerifyRule()
    {
        $parser = $this->createParserMock();

        $rule = new Rule\XPath('//a');

        $parser->addVerifyRule($rule, false);
        $this->assertSame([[$rule, false]], $parser->getVerifyRules());

        $parser->addVerifyRule($rule, true);
        $this->assertSame([[$rule, false], [$rule, true]], $parser->getVerifyRules());

        $parser->clearVerifyRules();
        $parser->addVerifyRule($rule, true);
        $this->assertSame([[$rule, true]], $parser->getVerifyRules());
    }

    public function testCreateVerifyRule()
    {
        $pattern = '//a';
        $rule = new Rule\XPath($pattern);

        $parser = $this->createParserMock();
        $parser->addVerifyRule($rule, false);
        $parser->addVerifyRule($rule, true);
        $parser->addVerifyRule($rule, true);

        $this->assertSame($pattern, $rule->getPattern());
        $this->assertInstanceOf('yCrawler\Parser\Rule', $rule);

        $rules = $parser->getVerifyRules();
        $this->assertInstanceOf('yCrawler\Parser\Rule', $rules[0][0]);
        $this->assertFalse($rules[0][1]);
        $this->assertTrue($rules[1][1]);
        $this->assertTrue($rules[2][1]);
    }

    public function testAddLinkRule()
    {
        $parser = $this->createParserMock();

        $rule = new Rule\XPath('//a');

        $parser->addLinkRule($rule);
        $this->assertSame([$rule], $parser->getLinkRules());

        $parser->addLinkRule($rule);
        $this->assertSame([$rule, $rule], $parser->getLinkRules());

        $parser->clearLinkRules();
        $parser->addLinkRule($rule);
        $this->assertSame([$rule], $parser->getLinkRules());
    }

    public function testCreateLinksRule()
    {
        $pattern = '//a';
        $rule = new Rule\XPath($pattern);

        $parser = $this->createParserMock();
        $parser->addLinkRule($rule);
        $parser->addLinkRule($rule);

        $this->assertSame($pattern, $rule->getPattern());

        $rules = $parser->getLinkRules();
        $this->assertInstanceOf('yCrawler\Parser\Rule', $rules[0]);
        $this->assertSame(2, count($rules));
    }

    public function testAddValueRule()
    {
        $parser = $this->createParserMock();

        $rule = new Rule\XPath('');

        $parser->addValueRule($rule, 'foo');
        $this->assertSame(['foo' => $rule], $parser->getValueRules());

        $parser->addValueRule($rule, 'bar');
        $this->assertSame(['foo' => $rule, 'bar' => $rule], $parser->getValueRules());

        $parser->clearValueRules();
        $parser->addValueRule($rule, 'bar');
        $this->assertSame(['bar' => $rule], $parser->getValueRules());
    }

    public function testAddGroupRule()
    {
        $parser = $this->createParserMock();

        $group = new Group();

        $parser->addValueGroup('foo', $group);
        $this->assertSame(['foo' => $group], $parser->getValueRules());

        $parser->addValueGroup('bar', $group);
        $this->assertSame(['foo' => $group, 'bar' => $group], $parser->getValueRules());

        $parser->clearValueRules();
        $parser->addValueGroup('bar', $group);
        $this->assertSame(['bar' => $group], $parser->getValueRules());
    }

    public function testSetOnParseCallback()
    {
        $closure = function($document) {
            return get_class($document);
        };

        $parser = $this->createParserMock();
        $parser->setOnParseCallback($closure);

        $this->assertSame($closure, $parser->getOnParseCallback());
    }
}