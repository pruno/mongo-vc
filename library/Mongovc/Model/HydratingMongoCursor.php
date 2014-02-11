<?php

namespace Mongovc\Model;

use Countable;
use Iterator;
use MongoCursor;

/**
 * Class HydratingMongoCursor
 * @package Application\Model
 */
class HydratingMongoCursor implements Countable,
                                      Iterator
{
    /**
     * @var MongoCursor
     */
    protected $cursor;

    /**
     * @var AbstractCollection
     */
    protected $collection;

    /**
     * @param MongoCursor $cursor
     * @param AbstractCollection $collection
     */
    public function __construct(MongoCursor $cursor, AbstractCollection $collection)
    {
        $this->cursor = $cursor;
        $this->collection = $collection;
    }

    /**
     * @return MongoCursor
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * @return AbstractCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->cursor->count();
    }

    /**
     * @return AbstractObject
     */
    public function current()
    {
        $result = $this->cursor->current();
        if (!is_array($result)) {
            return $result;
        }

        return $this->collection->createObjectFromRaw($result);
    }

    /**
     * @return string
     */
    public function key()
    {
        return $this->cursor->key();
    }

    public function next()
    {
        $this->cursor->next();
    }

    public function rewind()
    {
        $this->cursor->rewind();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->cursor->valid();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $results = array();
        foreach ($this->cursor as $data) {
            $results[] = $this->current();
        }

        $this->cursor->rewind();

        return $results;
    }
}