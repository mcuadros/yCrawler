<?php
namespace yCrawler;
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
    private $url;
    private $request;
    private $valuesStorage;
    private $linksStorage;

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
        $this->request = new Request($url);
    }

    public function getURL()
    {
        return $this->url;
    }

    public function getParser()
    {
        return $this->parser;
    }

    public function getHTML()
    {
        return $this->request->getResponse();
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

    protected function createLinksStorage()
    {
        if (!$this->isIndexable()) {
            $this->linksStorage = false;
        } else {
            $this->linksStorage = new LinksStorage($this->url, $this->parser);
            $this->fillLinksStorage();    
        }
    }

    public function getLinksStorage()
    {
        return $this->linksStorage;
    }

    protected function fillLinksStorage()
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
            $this->linksStorage->add($data['value']);
        }
    }

    public function createValuesStorage()
    {
        if (!$this->isVerified()) {
            $this->valuesStorage = false;
        } else {
            $this->valuesStorage = new ValuesStorage();
            $this->fillValuesStorage();
        }
    }

    public function getValuesStorage()
    {
        return $this->valuesStorage;
    }

    protected function fillValuesStorage()
    {
        foreach ($this->parser->getValueItems() as $key => $item) {
            $result = $item->evaluate($this);
            $this->saveEvaluationResult($key, $result);
        }
    }

    protected function saveEvaluationResult($key, $result)
    {
        $this->valuesStorage->set($key, $result);
    }

    public function parse()
    { 
        $this->makeRequest();
        
        $this->createValuesStorage();
        $this->createLinksStorage();
        $this->callOnParseCallback();

        $this->isParsed = true;
    }

    public function isParsed()
    {
        return $this->isParsed;
    }

    protected function makeRequest()
    {
        if ($this->isNeededExecuteRequest()) {
            $this->initizalizeParser();
            $this->executeRequest();
            $this->createDOM();
            $this->createXPath();
        }
    }

    protected function initizalizeParser()
    {
        $this->parser->configure();
    }

    protected function executeRequest()
    {
        $this->request->call();
    }

    protected function callOnParseCallback()
    {
        $cb = $this->parser->getOnParseCallback();
        if ($cb instanceOf Closure) {
            $cb($this);
        }
    }

    protected function isNeededExecuteRequest()
    {
        $status = $this->request->getStatus();
        if ($status != Request::STATUS_NONE) return false;

        return true;
    }

    protected function createDOM()
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $this->dom = new DOMDocument();
        $response = $this->getHTML();
        $response = $this->applyUTF8HackIfNeeded($response);

        if (!$this->dom->loadHtml($response)) { 
            throw new Exceptions\UnableToLoadHTML();
        }
    }

    protected function applyUTF8HackIfNeeded($html)
    {
        if (!Config::get('utf8_dom_hack')) return $html;

        return sprintf(
            '<?xml encoding="UTF-8">%s</xml>',
            str_ireplace('utf-8','', $html)
        );
    }

    protected function createXPath()
    {
        $this->xpath = new DOMXPath($this->dom);
        if (!$this->xpath) { 
            throw new Exceptions\UnableToCreateXPath();
        }
    }
}