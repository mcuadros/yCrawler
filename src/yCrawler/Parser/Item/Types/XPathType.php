<?php
namespace yCrawler\Parser\Item\Types;
use yCrawler\Parser\Exceptions;
use yCrawler\Document;

class XPathType implements Type
{
    public function evaluate(Document $document, $pattern)
    {
        $result = $this->evaluateXPath($document, $pattern);
        
        $output = Array();
        foreach ($result as $node) {
            $output[] = $this->createResultArrayForNode($document, $node);
        }

        return $output;
    }

    private function createResultArrayForNode(Document $document, $node)
    {
        return Array(
            'value' => $node->nodeValue,
            'node' => $node,
            'dom' => $document->getDOM()
        );
    }

    private function evaluateXPath(Document $document, $pattern)
    {
        $xpath = $document->getXPath();
        $result = $xpath->evaluate($pattern);

        if (!$result) {
            throw new Exceptions\MalformedExpression(sprintf(
                'Malformed XPath expression %s', $pattern
            ));
        }

        return $result;
    }
}