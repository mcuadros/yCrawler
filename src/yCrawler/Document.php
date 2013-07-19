<?php
namespace yCrawler;
use yCrawler\Parser\Item\Modifiers\Scalar;
use yCrawler\Misc\URL;
use yCrawler\Document\ValuesStorage;

use DOMDocument;
use DOMXPath;

class Document
{
    private $url;
    private $request;
    private $valuesStorage;

    protected $parser;
    protected $dom;
    protected $xpath;

    
    protected $links = Array();

    protected $verified;
    protected $indexable;
    protected $parsed;

    public function __construct($url, Parser $parser = null)
    {
        $this->url = $url;
        $this->valuesStorage = new ValuesStorage();
        $this->request = new Request($url);
        $this->parser = $parser;
    }

    public function evaluate()
    {
        $this->parser->configure();
        if (!$this->isVerified()) return false;

        foreach ($this->parser->getValueItems() as $key => $item) {
            $result = $item->evaluate($this);
            $this->saveEvaluationResult($key, $result);
        }
    }

    private function saveEvaluationResult($key, $result)
    {
        $this->valuesStorage->set($key, $result);
    }

    public function links()
    {
        $this->parser->configure();
        if (!$this->isIndexable()) return false;

        if (!$this->parser->getLinksItems() ) {
            $this->parser->createLinksItem('//a/@href');
        }

        foreach ($this->parser->getLinksItems() as $item) {
            foreach ($item->evaluate($this) as $data) {
                $url = URL::absolutize($data['value'], $this->url);
                if ($url && $this->parser->matchURL($url) ) $this->addLink($url);
            }
        }

        return $this->getLinks();
    }

    public function parse()
    {
        if ($this->parsed ) return $this;
        if ($this->parser->configure()) {
            $this->links();
            $this->evaluate();

            $this->parsed = true;
            $this->parser->parsed($this);
        }

        return $this;
    }

    public function evaluateXPath($expression)
    {
        if (!$this->makeRequest()) return false;

        $output = Array();
        $result = $this->xpath->evaluate($expression);

        if (!$result) return null;
        if ($result->length == 0)  return null;

        foreach ($result as $node) {
            $output[] = Array(
                'value' => $node->nodeValue,
                'node' => &$node,
                'dom' => &$this->dom
            );
        }

        if (count($output) == 0 ) return null;
        return $output;
    }

    public function evaluateRegExp($expression)
    {
        if (!$this->makeRequest()) return false;

        $response = $this->getResponseFromRequest();
        if (!preg_match_all($expression, $response, $matches)) return false;

        foreach (end($matches) as $index => $value) {
            $output[] = Array(
                'value' =>  $value,
                'full' =>  $matches[0][$index]
            );
        }

        if (count($output) == 0 ) return null;
        return $output;
    }

    public function getParser() { return $this->parser; }
    public function getLinks() { return array_keys($this->links); }
  
    public function isVerified()
    {
        if ($this->verified === null) $this->verified = $this->verify();
        return $this->verified;
    }

    public function isIndexable()
    {
        if ($this->indexable === null) $this->indexable = $this->follow();
        return $this->indexable;
    }

    public function isParsed() { return $this->parsed; }

    private function makeRequest()
    {
        if ($this->getStatusFromRequest() >= Request::STATUS_DONE) return true;
        if ($this->getStatusFromRequest() != Request::STATUS_NONE) return false;

        if (!$this->executeRequest()) return false;
        if (!$this->createDOM()) return false;
        if (!$this->createXPath()) return false;
        return true;
    }

    protected function createDOM()
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $this->dom = new DOMDocument();
        $response = $this->getResponseFromRequest();

        if (Config::get('utf8_dom_hack') ) {
            return $this->dom->loadHtml(sprintf(
                '<?xml encoding="UTF-8">%s</xml>',
                str_ireplace('utf-8','', $response)
            ));
        }

        return $this->dom->loadHtml($response);
    }

    protected function createXPath()
    {
        $this->xpath = new DOMXPath($this->dom);
        if ($this->xpath) { return true; } else { return false; }
    }

    protected function verify()
    {
        if (!$this->makeRequest()) return false;
        if (!$verifyItems = $this->parser->getVerifyItems()) return true;

        foreach ($verifyItems as &$item) {
            $item[0]->setModifier(Scalar::boolean($item[1]));
            if (!$item[0]->evaluate($this)) return false;
        }

        return true;
    }

    protected function follow()
    {
        if (!$this->makeRequest() ) return false;
        if (!$followItems = $this->parser->getFollowItems() ) return true;

        foreach ($followItems as &$item) {
            $item[0]->setModifier(Scalar::boolean($item[1]));
            if (!$item[0]->evaluate($this)) return false;
        }

        return true;
    }

    protected function addLink($url, $override = false)
    {
        if ($override) $this->links = array($url => true);
        else $this->links[$url] = true;
        return $url;
    }

    public function getValuesStorage()
    {
        return $this->valuesStorage;
    }

    private function getStatusFromRequest()
    {
        return $this->request->getStatus();
    }

    private function getResponseFromRequest()
    {
        return $this->request->getResponse();
    }

    private function executeRequest()
    {
        return $this->request->call();
    }
}
