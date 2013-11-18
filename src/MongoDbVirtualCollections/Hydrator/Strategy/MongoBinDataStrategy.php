<?php

namespace MongoDbVirtualCollections\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class MongoBinDataStrategy
 * @package MongoDbVirtualCollections\Hydrator\Strategy
 */
class MongoBinDataStrategy implements StrategyInterface
{
    /**
     * @param string|null $value
     * @return \MongoBinData
     */
    public function extract($value)
    {
        return $value !== null ? new \MongoBinData($value, \MongoBinData::CUSTOM) : null;
    }

    /**
     * @param \MongoBinData|null $value
     * @return string|null
     */
    public function hydrate($value)
    {
        return $value instanceof \MongoBinData ? $value->bin : null;
    }
}