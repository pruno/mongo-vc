<?php

namespace Mongovc\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class MongoTimestampStrategy
 * @package Mongovc\Hydrator\Strategy
 */
class MongoTimestampStrategy implements StrategyInterface
{
    /**
     * @param int|null $value
     * @return \MongoTimestamp
     */
    public function extract($value)
    {
        return $value !== null ? new \MongoTimestamp($value) : null;
    }

    /**
     * @param \MongoTimestamp|null $value
     * @return int|null
     */
    public function hydrate($value)
    {
        return $value instanceof \MongoTimestamp ? $value->sec : null;
    }
}