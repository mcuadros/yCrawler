<?php

namespace yCrawler;

use yCrawler\Document;
use yCrawler\Parser\Group;

use yCrawler\Parser\Exceptions;
use yCrawler\Parser\Rule;

abstract class Parser
{
    const URL_PATTERN_BASED_ON_DOMAIN = '~^https?://%s~';

    protected $initialized = false;
    protected $items = [
        'follow' => [], 'links' => [],
        'verify' => [], 'values' => []
    ];
    private $startup = [];
    private $urlPatterns = [];
    private $parseCallback;
    private $name;

    public function __construct($name)
    {
        $this->createDefaultURLPatterns();
        $this->name;
    }

    abstract public function initialize();

    public function configure()
    {
        if (!$this->isInitialized()) {
            $this->initialize();
            $this->initialized = true;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    public function getURLPatterns()
    {
        return $this->urlPatterns;
    }

    public function clearURLPatterns()
    {
        $this->urlPatterns = [];
    }

    public function setURLPattern($regexp, $clean = false)
    {
        if ($clean) {
            $this->clearURLPatterns();
        }

        if (!$this->validateRegExp($regexp)) {
            throw new Exceptions\InvalidURLPattern();
        }

        return $this->urlPatterns[] = $regexp;
    }

    public function matchURL($url)
    {
        if (count($this->urlPatterns) == 0) {
            $this->createDefaultURLPatterns();
        }

        foreach ($this->urlPatterns as $regexp) {
            if (preg_match($regexp, $url) === 1) {
                return true;
            }
        }

        return false;
    }

    public function getFollowRules()
    {
        return $this->items['follow'];
    }

    public function addLinkFollowRule(Rule $rule, $sign)
    {
        $this->items['follow'][] = [$rule, $sign];
    }

    public function clearFollowRules()
    {
        $this->items['follow'] = [];
    }

    public function getVerifyRules()
    {
        return $this->items['verify'];
    }

    public function addVerifyRule(Rule $rule, $sign)
    {
        $this->items['verify'][] = [$rule, $sign];
    }

    public function clearVerifyRules()
    {
        $this->items['verify'] = [];
    }

    public function getLinkRules()
    {
        return $this->items['links'];
    }

    public function addLinkRule(Rule $rule)
    {
        $this->items['links'][] = $rule;
    }

    public function clearLinkRules()
    {
        $this->items['links'] = [];
    }

    public function getValueRules()
    {
        return $this->items['values'];
    }

    public function addValueRule(Rule $rule, $name)
    {
        $this->items['values'][$name] = $rule;
    }

    public function clearValueRules()
    {
        $this->items['values'] = [];
    }

    public function addValueGroup($name, Group $group)
    {
        $this->items['values'][$name] = $group;
    }

    public function createValueGroup($name)
    {
        $group = new Group();

        $this->addValueGroup($name, $group);

        return $group;
    }

    public function setOnParseCallback(\Closure $closure)
    {
        $this->parseCallback = $closure;
    }

    public function getOnParseCallback()
    {
        return $this->parseCallback;
    }

    private function validateRegExp($regexp)
    {
        if (@preg_match($regexp, '') === false) {
            return false;
        }

        return true;
    }

    private function createDefaultURLPatterns()
    {
        $tmp = [];
        foreach ($this->startup as $url) {
            $tmp[] = $this->createURLPatternBasedOnURL($url);
        }

        $this->urlPatterns = array_unique($tmp);
    }

    private function createURLPatternBasedOnURL($url)
    {
        $domain = parse_url($url, PHP_URL_HOST);
        $domainWithEscapedDots = str_replace('.', '\.', $domain);

        return sprintf(self::URL_PATTERN_BASED_ON_DOMAIN, $domainWithEscapedDots);
    }
}
