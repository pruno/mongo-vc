<?php

namespace MongovcTests\Model\Collection;

use Mongovc\Model\AbstractSupportCollection;

/**
 * Class SupportCollection
 * @package MongovcTests\Model\Collection
 */
class SupportCollection extends AbstractSupportCollection
{
    protected $collectionName = 'virtuals';

    /*
     * This method is solely dedicated to tests
     */
    public function testInconsistentAliasType($alias)
    {
        $this->virtualCollections[$alias] = 1;

        return $this->getRegisteredVirtualCollection($alias);
    }

    /*
     * This method is solely dedicated to tests
     */
    public function testInvalidAliasResolution($alias)
    {
        $this->virtualCollections[$alias] = function() {
            return 1;
        };

        return $this->getRegisteredVirtualCollection($alias);
    }
}