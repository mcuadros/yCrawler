<?php

namespace yCrawler\Parser\Item\Types;

use yCrawler\Document;

interface Type
{
    public function evaluate(Document $document, $pattern);
}
