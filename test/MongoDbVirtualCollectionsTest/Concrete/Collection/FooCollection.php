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
     * @var string
     */
    protected $collectionName = 'foo';

    /**
     * @return Foo
     */
    public function createObjectPrototype()
    {
        return new Foo($this);
    }
}