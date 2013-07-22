<?php
namespace yCrawler;
use yCrawler\Parser\Item\Modifiers;
use yCrawler\Misc\URL;
use yCrawler\Document\ValuesStorage;
use yCrawler\Document\LinksStorage;
use yCrawler\Document\Exceptions;

use DOMDocument;
use DOMXPath;

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
        $this->makeRequest();
        return $this->request->getResponse();
    }

    public function getDOM()
    {
        $this->makeRequest();
        return $this->dom;
    }

    public function getXPath()
    {
        $this->makeRequest();
        return $this->xpath;
    }

    public function isIndexable()
    {
        $this->makeRequest();
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
        $this->makeRequest();
        if ($this->isVerified !== null) return $this->isVerified;
        
        $this->isVerified = true;

        $verifyItems = $this->parser->getVerifyItems();
        foreach ($verifyItems as &$item) {
            $this->isVerified = $this->evaluteItemAsScalar($item);
            if (!$this->isVerified) break;
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

    public function getLinksStorage()
    {
        if ($this->linksStorage === null) {
            if (!$this->isIndexable()) {
                $this->linksStorage = false;
            } else {
                $this->createLinksStorage();
                $this->fillLinksStorage();    
            }
        }

        return $this->linksStorage;
    }

    protected function createLinksStorage()
    {
        $this->linksStorage = new LinksStorage($this->url, $this->parser);
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

    public function getValuesStorage()
    {
        if ($this->valuesStorage === null) {
            if (!$this->isVerified()) {
                $this->valuesStorage = false;
            } else {
                $this->createValuesStorage();
                $this->fillValuesStorage();
            }
        }

        return $this->valuesStorage;
    }

    protected function createValuesStorage()
    {
        $this->valuesStorage = new ValuesStorage();
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
