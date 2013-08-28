<?php
namespace yCrawler\Parser\Item\Types;
use yCrawler\Parser\Exceptions;
use yCrawler\Document;

class LiteralType implements Type
{
    public function evaluate(Document $document, $pattern)
    {
        $output = Array(
            'value' =>  $pattern,
            'node' => null,
            'dom' => $document->getDOM()
        );
        
        return Array($output);
    }
}