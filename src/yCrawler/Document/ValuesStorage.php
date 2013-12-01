<?php

namespace yCrawler\Document;

use ArrayIterator;
use IteratorAggregate;
use Countable;

class ValuesStorage implements IteratorAggregate, Countable
{
    private $values;

    public function __construct()
    {
        $this->values = array();
    }

    public function all()
    {
        return $this->values;
    }

    public function keys()
    {
        return array_keys($this->values);
    }

    public function set($key, array $values)
    {
        $this->values[$key] = $values;
    }

    public function add($key, $value)
    {
        $this->values[$key][] = $value;
    }

    public function has($key)
    {
        return isset($this->values[$key]);
    }

    public function get($key)
    {
        if (!$this->has($key)) return false;
        return $this->values[$key];
    }

    public function remove($key)
    {
        unset($this->values[$key]);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->values);
    }

    public function count()
    {
        return count($this->values);
    }
}
