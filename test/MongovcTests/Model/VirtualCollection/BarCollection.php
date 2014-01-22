<?php

namespace MongovcTests\Model\VirtualCollection;

use Mongovc\Model\AbstractVirtualCollection;
use MongovcTests\Model\Object\Bar;

/**
 * Class BarCollection
 * @package MongovcTests\Model\VirtualCollection
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