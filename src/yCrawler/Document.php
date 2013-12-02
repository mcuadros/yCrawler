<?php

namespace yCrawler;

use yCrawler\Crawler\Request;
use yCrawler\Parser\Item\Modifiers;
use yCrawler\Misc\URL;
use yCrawler\Document\ValuesStorage;
use yCrawler\Document\LinksStorage;
use yCrawler\Document\Exceptions;

use DOMDocument;
use DOMXPath;
use Closure;

class Document
{
    protected $url;
    protected $values;
    protected $links;

    protected $markup;
    protected $parser;
    protected $dom;
    protected $xpath;

    protected $isVerified;
    protected $isIndexable;
    protected $isParsed;

    public function __construct($url, Parser $parser)
    {
        $this->url = $url;
        $this->parser = $parser;
    }

    public function getURL()
    {
        return $this->url;
    }

    public function getParser()
    {
        return $this->parser;
    }

    public function setMarkup($markup)
    {
        $this->markup = $markup;
    }

    public function getMarkup()
    {
        return $this->markup;
    }

    public function getDOM()
    {
        return $this->dom;
    }

    public function getXPath()
    {
        return $this->xpath;
    }

    public function isIndexable()
    {
        if ($this->isIndexable !== null) return $this->isIndexable;

        $this->isIndexable = true;

        $followItems = $this->parser->getFollowItems();
        foreach ($followItems as &$item) {
           $this->isIndexable = $this->evaluteItemAsScalar($item);
            if (!$this->isIndexable) break;
        }

        return $this->isIndexable;
    }

    public function isVerified()
    {
        if ($this->isVerified !== null) return $this->isVerified;

        $this->isVerified = true;

        $verifyItems = $this->parser->getVerifyItems();
        foreach ($verifyItems as &$item) {
            $this->isVerified = $this->evaluteItemAsScalar($item);
            if (!$this->isVerified) {
                break;
            }
        }

        return $this->isVerified;
    }

    protected function evaluteItemAsScalar(array &$item)
    {
        $item[0]->setModifier(Modifiers\Scalar::boolean($item[1]));
        if (!$item[0]->evaluate($this)) {
            return false;
        }

        return true;
    }

    public function getLinks()
    {
        return $this->links;
    }

    protected function collectLinks()
    {
        if (!$this->isIndexable()) {
            $this->links = false;
        } else {
            $this->links = new LinksStorage($this->url, $this->parser);
            $this->evaluateLinkRulesFromParser();
        }
    }

    protected function evaluateLinkRulesFromParser()
    {
        if (!$this->parser->getLinksItems() ) {
            $this->parser->createLinksItem('//a/@href');
        }

        foreach ($this->parser->getLinksItems() as $item) {
            $result = $item->evaluate($this);
            $this->saveLinksResult($result);
        }
    }

    protected function saveLinksResult($result)
    {
        foreach ($result as $data) {
            $this->links->add($data['value']);
        }
    }

    public function getValues()
    {
        return $this->values;
    }

    protected function collectValues()
    {
        if (!$this->isVerified()) {
            $this->values = false;
        } else {
            $this->values = new ValuesStorage();
            $this->evaluateValueRulesFromParser();
        }
    }

    protected function evaluateValueRulesFromParser()
    {
        foreach ($this->parser->getValueItems() as $key => $item) {
            $result = $item->evaluate($this);
            $this->saveEvaluationResult($key, $result);
        }
    }

    protected function saveEvaluationResult($key, $result)
    {
        $this->values->set($key, $result);
    }

    public function parse()
    {
        $this->initialize();

        $this->collectValues();
        $this->collectLinks();
        $this->executeOnParseCallback();

        $this->isParsed = true;
    }

    public function isParsed()
    {
        return $this->isParsed;
    }

    protected function initialize()
    {
        $this->initializeDOM();
        $this->initializeXPath();
        $this->initializeParser();
    }

    protected function initializeParser()
    {
        $this->parser->configure();
    }

    protected function initializeDOM()
    {
        $this->ifEmptyMarkupThrowException();

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new DOMDocument();
        $markup = $this->applyUTF8HackIfNeeded($this->markup);

        if (!$dom->loadHtml($markup)) {
            throw new Exceptions\UnableToLoadMarkup();
        }

        $this->dom = $dom;
    }

    protected function ifEmptyMarkupThrowException()
    {
        if (!$this->markup) {
            throw new Exceptions\InvalidMarkup();
        }
    }

    protected function applyUTF8HackIfNeeded($markup)
    {
        if (!Config::get('utf8_dom_hack')) {
            return $markup;
        }

        return sprintf(
            '<?xml encoding="UTF-8">%s</xml>',
            str_ireplace('utf-8', '', $markup)
        );
    }

    protected function initializeXPath()
    {
        $this->xpath = new DOMXPath($this->dom);
        if (!$this->xpath) {
            throw new Exceptions\UnableToCreateXPath();
        }
    }

    protected function executeOnParseCallback()
    {
        $cb = $this->parser->getOnParseCallback();
        if ($cb instanceOf Closure) {
            $cb($this);
        }
    }
}
