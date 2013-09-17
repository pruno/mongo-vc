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
}