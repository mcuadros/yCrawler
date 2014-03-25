<?php

namespace yCrawler;

use yCrawler\Document;
use yCrawler\Parser\Item;
use yCrawler\Parser\Group;

use yCrawler\Parser\Exceptions;

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

    public function getFollowItems()
    {
        return $this->items['follow'];
    }

    public function addLinkFollowItem(Item $item, $sign)
    {
        $this->items['follow'][] = [$item, $sign];
    }

    public function clearFollowItems()
    {
        $this->items['follow'] = [];
    }

    public function createLinkFollowItem($expression = false, $sign = true)
    {
        $item = new Item();
        if ($expression) $item->setPattern($expression);

        $this->addLinkFollowItem($item, $sign);

        return $item;
    }

    public function getVerifyItems()
    {
        return $this->items['verify'];
    }

    public function addVerifyItem(Item $item, $sign)
    {
        $this->items['verify'][] = Array($item, $sign);
    }

    public function clearVerifyItems()
    {
        $this->items['verify'] = [];
    }

    public function createVerifyItem($expression = false, $sign = true)
    {
        $item = new Item();
        if ($expression) $item->setPattern($expression);

        $this->addVerifyItem($item, $sign);

        return $item;
    }

    public function getLinksItems()
    {
        return $this->items['links'];
    }

    public function addLinksItem(Item $item)
    {
        $this->items['links'][] = $item;
    }

    public function clearLinksItems()
    {
        $this->items['links'] = [];
    }

    public function createLinksItem($expression = false)
    {
        $item = new Item();
        if ($expression) $item->setPattern($expression);

        $this->addLinksItem($item);

        return $item;
    }

    public function getValueItems()
    {
        return $this->items['values'];
    }

    public function addValueItem($name, Item $item)
    {
        $this->items['values'][$name] = $item;
    }

    public function clearValueItems()
    {
        $this->items['values'] = [];
    }

    public function createValueItem($name, $expression = false)
    {
        $item = new Item();
        if ($expression) $item->setPattern($expression);

        $this->addValueItem($name, $item);

        return $item;
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
