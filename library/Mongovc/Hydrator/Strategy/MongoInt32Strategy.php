<?php

namespace Mongovc\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class MongoInt32Strategy
 * @package Mongovc\Hydrator\Strategy
 */
class MongoInt32Strategy implements StrategyInterface
{
    /**
     * @param string|null $value
     * @return \MongoInt32
     */
    public function extract($value)
    {
        return $value !== null ? new \MongoInt32($value) : null;
    }

    /**
     * @param \MongoInt32|null $value
     * @return string|null
     */
    public function hydrate($value)
    {
        return $value instanceof \MongoInt32 ? $value->value : null;
    }
}