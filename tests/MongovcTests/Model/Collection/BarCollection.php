<?php

namespace MongovcTests\Model\Collection;

use Mongovc\Model\AbstractVirtualCollection;
use MongovcTests\Model\Object\Bar;

/**
 * Class BarCollection
 * @package MongovcTests\Model\Collection
 *
 * @method \MongovcTests\Model\Object\Bar createObject()
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