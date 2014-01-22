<?php

namespace MongovcTests\Model\VirtualCollection;

use Mongovc\Model\AbstractVirtualCollection;
use MongovcTests\Model\Object\Foo;

/**
 * Class FooCollection
 * @package MongovcTests\Model\VirtualCollection
 */
class FooCollection extends AbstractVirtualCollection
{
    /**
     * @var string
     */
    const ALIAS = 'Foos';

    /**
     * @return Foo
     */
    public function createObjectPrototype()
    {
        return new Foo($this);
    }
}