<?php

namespace MongoDbVirtualCollections\Model;

use Countable;
use InvalidArgumentException;
use Iterator;
use MongoCursor;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class HydratingMongoCursor
 * @package Application\Model
 */
class HydratingMongoCursor implements Countable, Iterator
{
    /**
     * @var \MongoCursor
     */
    protected $cursor;

    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected $hydrator;

    /**
     * @var object
     */
    protected $prototype;

    /**
     * @param MongoCursor $cursor
     * @param HydratorInterface $hydrator
     * @param AbstractObject $prototype
     * @throw InvalidArgumentException
     */
    public function __construct(MongoCursor $cursor, HydratorInterface $hydrator, AbstractObject $prototype)
    {
        $this->cursor = $cursor;
        $this->hydrator = $hydrator;

        if (!($prototype instanceof AbstractObject)) {
            throw new InvalidArgumentException(sprintf(
                'Prototype must be an instance of AbstractObject; received "%s"',
                gettype($prototype)
            ));
        }
        $this->prototype = $prototype;
    }

    /**
     * @return MongoCursor
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * @return object
     */
    public function getPrototype()
    {
        return $this->prototype;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->cursor->count();
    }

    /**
     * @return object
     */
    public function current()
    {
        $result = $this->cursor->current();
        if (!is_array($result)) {
            return $result;
        }

        return $this->hydrator->hydrate($result, clone $this->prototype);
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
}