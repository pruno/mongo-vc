<?php

namespace MongovcTests\Model\VirtualCollection;

use Mongovc\Model\AbstractVirtualCollection;
use MongovcTests\Model\Object\Baz;

/**
 * Class BazCollection
 * @package MongovcTests\Model\VirtualCollection
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