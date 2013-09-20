<?php

namespace MongoDbVirtualCollectionsTest\Concrete\Collection;

use MongoDbVirtualCollections\Model\AbstractCollection;
use MongoDbVirtualCollectionsTest\Concrete\Object\Foo;

/**
 * Class FooCollection
 * @package MongoDbVirtualCollectionsTest\Concrete\Collection
 */
class FooCollection extends AbstractCollection
{
    /**
     * @return string
     */
    public function getCollectionName()
    {
        return 'foos';
    }

    /**
     * @return Foo
     */
    protected function createObjectPrototype()
    {
        return new Foo($this->getServiceLocator(), $this);
    }

    /**
     * @return array
     */
    public function getAssetSchema()
    {
        return array(
            'foo1',
            'foo2'
        );
    }
}