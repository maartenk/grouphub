<?php

namespace AppBundle;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * Class Sequence
 */
class Sequence implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var array
     */
    protected $elements;

    /**
     * @param array $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = array_values($elements);
    }

    /**
     * @param int   $index
     * @param mixed $value
     */
    public function insertAt($index, $value)
    {
        array_splice($this->elements, $index, 0, [$value]);
    }

    /**
     * @param int $index
     */
    public function removeAt($index)
    {
        if (!$this->offsetExists($index)) {
            throw new InvalidArgumentException('Invalid index');
        }

        array_splice($this->elements, $index, 1);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($index)
    {
        return array_key_exists($index, $this->elements);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($index)
    {
        if (!$this->offsetExists($index)) {
            throw new InvalidArgumentException('Invalid index');
        }

        return $this->elements[$index];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($index, $value)
    {
        $this->elements[$index] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($index)
    {
        $this->removeAt($index);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->elements;
    }
}
