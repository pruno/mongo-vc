<?php

namespace MongovcTests;

use MongovcTests\Model\Collection\FooCollection;

/**
 * Class CollectionTest
 * @package MongovcTests
 */
class CollectionTest extends AbstractCollectionTest
{
    /**
     * @return FooCollection
     */
    public function createCollection()
    {
        return new FooCollection($this->getDriver());
    }
}