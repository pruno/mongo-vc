<?php

namespace MongoDbVirtualCollections\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class MongoMinKeyStrategy
 * @package MongoDbVirtualCollections\Hydrator\Strategy
 */
class MongoMinKeyStrategy implements StrategyInterface
{
    /**
     * In order to allow inserting null value instead of a \MongoMinKey boolean FALSE must be passed
     *
     * @param mixed $value
     * @return \MongoMinKey
     */
    public function extract($value)
    {
        return $value !== false ? new \MongoMinKey() : null;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function hydrate($value)
    {
        return $value;
    }
}