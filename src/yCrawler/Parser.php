<?php

namespace yCrawler;

use yCrawler\Document;
use yCrawler\Misc\URL;
use yCrawler\Parser\Item;
use yCrawler\Parser\Group;

use yCrawler\Parser\Exceptions;

/**
 * Parser_Base es una clase abstracta que ha de ser extendida por una clase final a modo de
 * configuración, donde se definirán todos los permenores para cada parser.
 *
 * <code>
 * namespace Config;
 * use yCrawler;
 *
 * class Localhost extends yCrawler\Parser_Base {
 *      public function initialize() {
 *          $this->setURLPattern('/localhost/');
 *
 *          //config...
 *      }
 * }
 * </code>
 *
 */
abstract class Parser
{
    const URL_PATTERN_BASED_ON_DOMAIN = '~^https?://%s~';

    protected $initialized = false;
    private $startup = Array();
    private $urlPatterns = Array();
    private $parseCallback;
    private $name;
    protected $items = Array(
        'follow' => array(), 'links' => array(),
        'verify' => array(), 'values' => array()
    );

    abstract public function initialize();

    public function configure()
    {
        if (!$this->isInitialized()) {
            $this->initialize();
            $this->initialized = true;
        }
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }

        return get_class($this);
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    public function getStartupURLs()
    {
        return $this->startup;
    }

    public function clearStartupURLs()
    {
        $this->startup = array();
    }

    public function setStartupURL($url, $clean = false)
    {
        if ($clean) {
            $this->clearStartupURLs();
        }

        if (!URL::validate($url)) {
            throw new Exceptions\InvalidStartupURL();
        }

        return $this->startup[] = $url;
    }

    public function getStartupDocs()
    {
        $this->configure();

        $documents = array();
        foreach ($this->startup as $url) {
            $documents[] = new Document($url, $this);
        }

        return $documents;
    }

    public function getURLPatterns()
    {
        return $this->urlPatterns;
    }

    public function clearURLPatterns()
    {
        $this->urlPatterns = array();
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

    private function validateRegExp($regexp)
    {
        if (@preg_match($regexp, '') === false) {
            return false;
        }

        return true;
    }

    public function matchURL($url)
    {
        if (count($this->urlPatterns) == 0) {
            $this->createDefaultURLPatterns();
        }

        foreach ($this->urlPatterns as $regexp) {
            if (preg_match($regexp, $url) === 1) return true;
        }

        return false;
    }

    private function createDefaultURLPatterns()
    {
        $tmp = Array();
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

    public function getFollowItems()
    {
        return $this->items['follow'];
    }

    public function addLinkFollowItem(Item $item, $sign)
    {
        $this->items['follow'][] = Array($item, $sign);
    }

    public function clearFollowItems()
    {
        $this->items['follow'] = Array();
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
        $this->items['verify'] = Array();
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
        $this->items['links'] = Array();
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
        $this->items['values'] = Array();
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
}
