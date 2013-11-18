<?php

namespace MongoDbVirtualCollections\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Class MongoDate
 * @package MongoDbVirtualCollections\Hydrator\Strategy
 */
class MongoDateStrategy implements StrategyInterface
{
    /**
     * @var string
     */
    protected $format;

    /**
     * @param $format
     */
    public function __construct($format)
    {
        $this->format = $format;
    }

    /**
     * @param string|null $value
     * @return \MongoDate
     */
    public function extract($value)
    {
        return $value ? new \MongoDate(strtotime($value)) : null;
    }

    /**
     * @param int $value
     * @return \MongoDate|null
     */
    public function hydrate($value)
    {
        /* @var $value \MongoDate */
        return $value ? date($this->format, $value->sec) : null;
    }
}