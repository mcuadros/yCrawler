<?php

namespace yCrawler\Parser\Rule;

use yCrawler\Document;
use yCrawler\Parser\Rule;

class Literal extends Rule
{
    protected function doEvaluate(Document $document)
    {
        $output = [
            'value' =>  $this->pattern,
            'node' => null,
            'dom' => $document->getDOM()
        ];

        return [$output];
    }
}
