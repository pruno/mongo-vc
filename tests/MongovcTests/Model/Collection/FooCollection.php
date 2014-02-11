<?php

namespace MongovcTests\Model\Collection;

use Mongovc\Model\AbstractVirtualCollection;
use MongovcTests\Model\Object\Foo;

/**
 * Class FooCollection
 * @package MongovcTests\Model\Collection
 *
 * @method \MongovcTests\Model\Object\Foo createObject()
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