<?php
namespace yCrawler\Parser;
use yCrawler\Document;

class Item
{
    const TYPE_XPATH = 'xpath';
    const TYPE_REGEXP = 'regexp';
    const TYPE_LITERAL = 'literal';

    private $pattern;
    private $type = self::TYPE_XPATH;
    private $attribute;
    private $modifiers;
    private $callback;

    public function getType() { return $this->type; }
    public function setType($type)
    {
        if (
            $type != self::TYPE_XPATH &&
            $type != self::TYPE_REGEXP &&
            $type != self::TYPE_LITERAL
        ) {
            throw new \InvalidArgumentException(sprintf('Invalid type "%s"' , $type));
        }

        $this->type = $type;

        return $this;
    }

    public function getPattern() { return $this->pattern; }
    public function setPattern($pattern)
    {
        if ( $this->type == self::TYPE_REGEXP ) $this->validateRegExp($pattern);

        $this->pattern = $pattern;

        return $this;
    }

    public function getModifiers() { return $this->modifiers; }
    public function setModifier(\Closure $modifier)
    {
        $this->modifiers[] = $modifier;

        return $this;
    }

    public function evaluate(Document $document)
    {
        switch ($this->type) {
            case self::TYPE_XPATH:
                $result = $document->evaluateXPath($this->pattern);
                break;
            case self::TYPE_REGEXP:
                $result = $document->evaluateRegExp($this->pattern);
                break;
            case self::TYPE_LITERAL:
                $result = Array(Array('value'=> $this->pattern));
                break;
        }

        if ( !$result ) $result = array();
        $this->applyModifiers($result, $document);

        return $result;
    }

    private function applyModifiers(&$result, Document &$document)
    {
        if ( !$this->modifiers ) return true;
        foreach( $this->modifiers as $modifier) $modifier($result, $document);

        return true;
    }

    private function validateRegExp($regexp)
    {
        if ( @preg_match($regexp, '') !== false) return true;
        throw new \InvalidArgumentException(sprintf('Invalid regexp "%s"' , $regexp));

        return true;
    }

    public function __toString()
    {
        return '["' . $this->pattern . '"][' . $this->type . ']' . json_encode($this->modifier);
    }
}
