<?php

namespace yCrawler;

use yCrawler\Parser\Rule\Modifiers;
use yCrawler\Document\ValuesStorage;
use yCrawler\Document\LinksStorage;
use yCrawler\Document\Exceptions;
use DOMDocument;
use DOMXPath;
use Closure;
use yCrawler\Parser\Rule\XPath;
use yCrawler\Crawler\Request\Config as RConfig;

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
        if ($this->isIndexable !== null) {
            return $this->isIndexable;
        }

        $this->isIndexable = true;

        $followRules = $this->parser->getFollowRules();
        foreach ($followRules as &$rule) {
            $this->isIndexable = $this->evaluateItemAsScalar($rule)['value'];
            if (!$this->isIndexable) {
                break;
            }
        }

        return $this->isIndexable;
    }

    public function isVerified()
    {
        if ($this->isVerified !== null) {
            return $this->isVerified;
        }

        $this->isVerified = true;

        $verifyRules = $this->parser->getVerifyRules();
        foreach ($verifyRules as &$rule) {
            $this->isVerified = $this->evaluateItemAsScalar($rule)['value'];
            if (!$this->isVerified) {
                break;
            }
        }

        return $this->isVerified;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function getValues()
    {
        return $this->values;
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

    protected function collectLinks()
    {
        $this->links = null;
        if ($this->isIndexable()) {
            $this->links = new LinksStorage($this->url, $this->parser);
            $this->evaluateLinkRulesFromParser();
        }
    }

    protected function evaluateLinkRulesFromParser()
    {
        if (!$this->parser->getLinkRules()) {
            $this->parser->addLinkRule(new XPath('//a/@href'));
        }

        foreach ($this->parser->getLinkRules() as $rule) {
            $result = $rule->evaluate($this);
            $this->saveLinksResult($result);
        }
    }

    protected function saveLinksResult($result)
    {
        foreach ($result as $data) {
            $this->links->add($data['value']);
        }
    }

    protected function collectValues()
    {
        $this->values = false;

        if ($this->isVerified()) {
            $this->values = new ValuesStorage();
            $this->evaluateValueRulesFromParser();
        }
    }

    protected function evaluateValueRulesFromParser()
    {
        foreach ($this->parser->getValueRules() as $key => $rule) {
            $result = $rule->evaluate($this);
            $this->values->set($key, $result);
        }
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
        $this->isValidMarkup();

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new DOMDocument();
        $markup = $this->applyUTF8HackIfNeeded($this->markup);

        if (!$dom->loadHtml($markup)) {
            throw new Exceptions\UnableToLoadMarkup();
        }

        $this->dom = $dom;
    }

    protected function isValidMarkup()
    {
        if (!$this->markup) {
            throw new Exceptions\InvalidMarkup();
        }
    }

    protected function applyUTF8HackIfNeeded($markup)
    {
        if (!RConfig::get('utf8_dom_hack')) {
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
        if ($cb instanceof Closure) {
            $cb($this);
        }
    }

    protected function evaluateItemAsScalar(array &$rule)
    {
        $rule[0]->addModifier(Modifiers\Scalar::boolean($rule[1]));
        return $rule[0]->evaluate($this)[0];
    }
}
