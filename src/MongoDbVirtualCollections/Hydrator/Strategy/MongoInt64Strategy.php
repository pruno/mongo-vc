<?php

namespace MongoDbVirtualCollections\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class MongoInt64Strategy
 * @package MongoDbVirtualCollections\Hydrator\Strategy
 */
class MongoInt64Strategy implements StrategyInterface
{
    /**
     * @param string|null $value
     * @return \MongoInt64
     */
    public function extract($value)
    {
        return $value !== null ? new \MongoInt64($value) : null;
    }

    /**
     * @param \MongoInt64|null $value
     * @return string|null
     */
    public function hydrate($value)
    {
        return $value instanceof \MongoInt64 ? $value->value : null;
    }
}