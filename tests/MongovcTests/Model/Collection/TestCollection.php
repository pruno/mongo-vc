<?php

namespace MongovcTests\Model\Collection;

use Mongovc\Model\AbstractCollection;
use MongovcTests\Model\Object\Foo;

/**
 * Class TestCollection
 * @package MongovcTests\Model\Collection
 */
class TestCollection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $collectionName = 'test';

    /**
     * @return Foo
     */
    public function createObjectPrototype()
    {
        return new Foo($this);
    }
}