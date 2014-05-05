<?php

namespace yCrawler\Parser\Rule;

use yCrawler\Parser\Exceptions;
use yCrawler\Document;
use yCrawler\Parser\Rule;

class RegExp extends Rule
{
    protected function doEvaluate(Document $document)
    {
        $result = $this->evaluateRegExp($document);

        $output = [];
        foreach (end($result) as $index => $value) {
            $output[] = [
                'value' =>  $value,
                'full' =>  $result[0][$index],
                'node' => null,
                'dom' => $document->getDOM()
            ];
        }

        return $output;
    }

    private function evaluateRegExp(Document $document)
    {
        $html = $document->getMarkup();

        if (!preg_match_all($this->pattern, $html, $matches)) {
            throw new Exceptions\MalformedExpression(sprintf('Malformed RegExp expression %s', $this->pattern));
        }

        return $matches;
    }
}
