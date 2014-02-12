<?php

namespace MongovcTests;

use Mongovc\Hydrator\Strategy\MongoIdStrategy;
use MongovcTests\Model\Collection\FooCollection;
use Zend\Stdlib\Hydrator\ObjectProperty;

/**
 * Class Issue11Collection
 * @package MongovcTests
 */
class Issue11Collection extends FooCollection
{
    /**
     * @return ObjectProperty
     */
    protected function createHydrator()
    {
        $hydrator = new ObjectProperty();
        $hydrator->addStrategy('_id', new MongoIdStrategy());

        return $hydrator;
    }
}