<?php

namespace yCrawler\Parser\Item\Types;

use Symfony\Component\CssSelector\CssSelector;
use yCrawler\Document;

class CSSType extends XPathType
{
    public function evaluate(Document $document, $pattern)
    {
        $xpathPattern = $this->convertCSSPatternToXpath($pattern);

        return parent::evaluate($document, $xpathPattern);
    }

    private function convertCSSPatternToXpath($cssPattern)
    {
        $xpathPattern = CssSelector::toXPath($cssPattern);

        return $xpathPattern;
    }

}
