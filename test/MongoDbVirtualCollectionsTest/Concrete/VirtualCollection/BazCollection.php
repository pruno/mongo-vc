<?php

namespace MongoDbVirtualCollectionsTest\Concrete\VirtualCollection;

use MongoDbVirtualCollections\Model\AbstractVirtualCollection;
use MongoDbVirtualCollectionsTest\Concrete\Object\Baz;

/**
 * Class BazCollection
 * @package MongoDbVirtualCollectionsTest\Concrete\VirtualCollection
 */
class BazCollection extends AbstractVirtualCollection
{
    /**
     * @var string
     */
    const ALIAS = 'Bazs';

    /**
     * @return Baz
     */
    public function createObjectPrototype()
    {
        return new Baz($this);
    }
}