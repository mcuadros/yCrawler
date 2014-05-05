<?php

namespace yCrawler\Parser;

use yCrawler\Document;
use yCrawler\SerializableClosure;
use Closure;

abstract class Rule
{
    protected $pattern;
    private $modifiers = [];

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    abstract protected function doEvaluate(Document $document);

    public function getPattern()
    {
        return $this->pattern;
    }

    public function addModifier(Closure $modifier)
    {
        $this->modifiers[] = new SerializableClosure($modifier);

        return $this;
    }

    public function getModifiers()
    {
        return $this->modifiers;
    }

    public function evaluate(Document $document)
    {
        $result = $this->doEvaluate($document);

        if (!$result) {
            return $result[]['value'] = false;
        }

        $this->applyModifiers($result, $document);

        return $result;
    }

    private function applyModifiers(&$result, \yCrawler\Document $document)
    {
        foreach ($this->modifiers as $modifier) {
            $result = $modifier($result, $document);
        }
    }

    public function __toString()
    {
        return '["' . $this->pattern . '"][' . get_class($this) . ']' . json_encode($this->modifiers);
    }
}
