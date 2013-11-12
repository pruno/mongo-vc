<?php

namespace MongoDbVirtualCollectionsTest\Concrete\VirtualCollection;

use MongoDbVirtualCollections\Model\AbstractVirtualCollection;
use MongoDbVirtualCollectionsTest\Concrete\Object\Foo;

/**
 * Class FooCollection
 * @package MongoDbVirtualCollectionsTest\Concrete\VirtualCollection
 */
class FooCollection extends AbstractVirtualCollection
{
    /**
     * @var string
     */
    protected $alias = 'Foos';

    /**
     * @return Foo
     */
    public function createObjectPrototype()
    {
        return new Foo($this);
    }
}