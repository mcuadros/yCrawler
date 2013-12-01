<?php

namespace yCrawler\Parser;

use yCrawler\Parser\Item\Types\Type;
use yCrawler\Document;
use ReflectionClass;
use yCrawler\SerializableClosure;
use Closure;

class Item
{
    const TYPE_CSS = 'yCrawler\Parser\Item\Types\CSSType';
    const TYPE_XPATH = 'yCrawler\Parser\Item\Types\XPathType';
    const TYPE_REGEXP = 'yCrawler\Parser\Item\Types\RegExpType';
    const TYPE_LITERAL = 'yCrawler\Parser\Item\Types\LiteralType';

    private $pattern;
    private $type = self::TYPE_XPATH;
    private $attribute;
    private $modifiers;
    private $callback;

    public function setType($type)
    {
        $this->isValidType($type);
        $this->type = $type;

        return $this;
    }

    private function isValidType($typeValue)
    {
        $r = new ReflectionClass(__CLASS__);
        foreach ($r->getConstants() as $const => $value) {
            if ($value === $typeValue) {
                return true;
            }
        }

        throw new \InvalidArgumentException(sprintf('Invalid type "%s"' , $typeValue));
    }

    public function getType()
    {
        return $this->type;
    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setModifier(Closure $modifier)
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
        $type = new $this->type();
        $result = $type->evaluate($document, $this->pattern);

        if (!$result) $result = array();
        $this->applyModifiers($result, $document);

        return $result;
    }

    private function applyModifiers(&$result, Document $document)
    {
        if (!$this->modifiers) return true;
        foreach( $this->modifiers as $modifier) $modifier($result, $document);

        return true;
    }

    public function __toString()
    {
        return '["' . $this->pattern . '"][' . $this->type . ']' . json_encode($this->modifier);
    }
}
