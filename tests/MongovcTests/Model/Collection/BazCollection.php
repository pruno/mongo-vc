<?php

namespace MongovcTests\Model\Collection;

use Mongovc\Model\AbstractVirtualCollection;
use MongovcTests\Model\Object\Baz;

/**
 * Class BazCollection
 * @package MongovcTests\Model\Collection
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