<?php

namespace yCrawler\Parser\Rule;

use Symfony\Component\CssSelector\CssSelector;
use yCrawler\Document;

class CSS extends XPath
{
    private $cssPattern;

    public function getPattern()
    {
        return $this->cssPattern;
    }

    protected function doEvaluate(Document $document)
    {
        $this->cssPattern = $this->pattern;
        $this->pattern = $this->convertCSSPatternToXpath($this->pattern);

        return parent::doEvaluate($document);
    }

    private function convertCSSPatternToXpath($cssPattern)
    {
        $xpathPattern = CssSelector::toXPath($cssPattern);

        return $xpathPattern;
    }
}
