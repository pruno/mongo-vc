<?php

namespace MongoDbVirtualCollectionsTest\Concrete\SupportCollection;

use MongoDbVirtualCollections\Model\AbstractSupportCollection;

/**
 * Class SupportCollection
 * @package MongoDbVirtualCollectionsTest\Concrete\SupportCollection
 */
class SupportCollection extends AbstractSupportCollection
{
    /**
     * @return string
     */
    public function getCollectionName()
    {
        return 'virtuals';
    }
}