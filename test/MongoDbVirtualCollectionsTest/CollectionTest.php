<?php

namespace MongoDbVirtualCollectionsTest;

use MongoDbVirtualCollectionsTest\Concrete\Collection\FooCollection;

/**
 * Class CollectionTest
 * @package MongoDbVirtualCollectionsTest
 */
class CollectionTest extends AbstractCollectionTest
{
    /**
     * @return FooCollection
     */
    public function createCollection()
    {
        return new FooCollection($this->getServiceLocator(), $this->getDriver());
    }
}