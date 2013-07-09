<?php
namespace yCrawler\Parser;
use yCrawler\Parser\Item;
use yCrawler\Document;

class Group
{
    private $items = Array();
    private $modifiers;

    public function createItem($expression = false)
    {
        $item = new Item();
        if ($expression) $item->setPattern($expression);
        $this->items[] = $item;

        return $item;
    }

    public function evaluate(Document $document)
    {
        $output = Array();
        foreach ($this->items as $item) {
            foreach($item->evaluate($document) as $data) $output[] = $data;
        }

        $this->applyModifiers($output, $document);

        return $output;
    }

    public function getModifiers() { return $this->modifiers; }
    public function setModifier(\Closure $modifier)
    {
        $this->modifiers[] = $modifier;

        return $this;
    }

    private function applyModifiers(&$result, Document &$document)
    {
        if (!$this->modifiers) return true;
        foreach( $this->modifiers as $modifier) $modifier($result, $document);

        return true;
    }

    public function __toString()
    {
        return (string) var_export($this);
    }
}
