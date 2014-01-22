<?php

namespace MongovcTests\Model\Collection;

use Mongovc\Model\AbstractCollection;
use MongovcTests\Model\Object\Foo;

/**
 * Class FooCollection
 * @package MongovcTests\Model\Collection
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