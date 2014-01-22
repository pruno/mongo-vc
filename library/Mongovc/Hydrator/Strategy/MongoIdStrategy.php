<?php

namespace Mongovc\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class MongoIdStrategy
 * @package Mongovc\Hydrator\Strategy
 */
class MongoIdStrategy implements StrategyInterface
{
    /**
     * @param mixed $value
     * @return \MongoId
     */
    public function extract($value)
    {
        return $value !== null ? new \MongoId($value) : null;
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