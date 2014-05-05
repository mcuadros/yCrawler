<?php

namespace yCrawler\Parser\Rule;

use yCrawler\Parser\Exceptions;
use yCrawler\Document;
use yCrawler\Parser\Rule;

class XPath extends Rule
{
    protected function doEvaluate(Document $document)
    {
        $nodeList = $this->evaluateXPath($document);

        $output = [];
        if ($nodeList->length < 1) {
            $output[] = $this->createEmptyResult();
        }

        foreach ($nodeList as $node) {
            $output[] = $this->createResultArrayForNode($document, $node);
        }

        return $output;
    }

    private function createResultArrayForNode(Document $document, $node)
    {
        return [
            'value' => $node->nodeValue,
            'node' => $node,
            'raw' => $document->getDOM()->saveXML($node)
        ];
    }

    private function createEmptyResult()
    {
        return [
            'value' => '',
            'node' => new \DOMElement('empty'),
            'raw' => ''
        ];
    }

    private function evaluateXPath(Document $document)
    {
        $xpath = $document->getXPath();
        $result = $xpath->evaluate($this->pattern);

        if (!$result) {
            throw new Exceptions\MalformedExpression(sprintf('Malformed XPath expression "%s"', $this->pattern));
        }

        return $result;
    }
}
