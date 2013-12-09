<?php

namespace MongoDbVirtualCollectionsTest\Concrete\VirtualCollection;

use MongoDbVirtualCollections\Model\AbstractVirtualCollection;
use MongoDbVirtualCollectionsTest\Concrete\Object\Bar;

/**
 * Class BarCollection
 * @package MongoDbVirtualCollectionsTest\Concrete\VirtualCollection
 */
class BarCollection extends AbstractVirtualCollection
{
    /**
     * @var string
     */
    const ALIAS = 'Bars';

    /**
     * @return Bar
     */
    public function createObjectPrototype()
    {
        return new Bar($this);
    }
}