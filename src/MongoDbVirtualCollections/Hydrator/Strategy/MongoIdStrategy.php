<?php

namespace MongoDbVirtualCollections\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class MongoIdStrategy
 * @package MongoDbVirtualCollections\Hydrator\Strategy
 */
class MongoIdStrategy implements StrategyInterface
{
    /**
     * @param mixed $value
     * @return \MongoId
     */
    public function extract($value)
    {
        return new \MongoId($value);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function hydrate($value)
    {
        return (string) $value;
    }
}