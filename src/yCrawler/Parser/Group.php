<?php

namespace yCrawler\Parser;

use yCrawler\Parser\Item;
use yCrawler\Document;
use yCrawler\SerializableClosure;
use Closure;

class Group
{
    private $items = [];
    private $modifiers = [];

    public function createItem($expression = false)
    {
        $item = new Item();
        if ($expression) {
            $item->setPattern($expression);
        }
        $this->items[] = $item;

        return $item;
    }

    public function evaluate(Document $document)
    {
        $output = [];
        foreach ($this->items as $item) {
            foreach($item->evaluate($document) as $data) {
                $output[] = $data;
            }
        }

        return $this->applyModifiers($output, $document);
    }

    public function getModifiers()
    {
        return $this->modifiers;
    }

    public function setModifier(Closure $modifier)
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
