<?php

namespace Mongovc\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class MongoMaxKeyStrategy
 * @package Mongovc\Hydrator\Strategy
 */
class MongoMaxKeyStrategy implements StrategyInterface
{
    /**
     * In order to allow inserting null value instead of a \MongoMaxKey boolean FALSE must be passed
     *
     * @param mixed $value
     * @return \MongoMaxKey
     */
    public function extract($value)
    {
        return $value !== false ? new \MongoMaxKey() : null;
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