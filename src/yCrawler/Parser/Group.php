<?php

namespace yCrawler\Parser;

use yCrawler\Parser\Item;
use yCrawler\Document;
use yCrawler\SerializableClosure;
use Closure;

class Group
{
    private $rules = [];
    private $modifiers = [];

    public function addRule(Rule $rule)
    {
        $this->rules[] = $rule;

        return $this;
    }

    public function evaluate(Document $document)
    {
        $output = [];
        foreach ($this->rules as $rule) {
            foreach($rule->evaluate($document) as $data) {
                $output[] = $data;
            }
        }

        return $this->applyModifiers($output, $document);
    }

    public function getModifiers()
    {
        return $this->modifiers;
    }

    public function addModifier(Closure $modifier)
    {
        $this->modifiers[] = new SerializableClosure($modifier);

        return $this;
    }

    public function __toString()
    {
        return (string) var_export($this);
    }

    private function applyModifiers($result, Document $document)
    {
        foreach($this->modifiers as $modifier) {
            $result = $modifier($result, $document);
        }

        return $result;
    }
}
