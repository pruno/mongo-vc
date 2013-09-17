<?php

namespace MongoDbVirtualCollectionsTest\Concrete\Object;

use MongoDbVirtualCollections\Model\AbstractObject;

/**
 * Class Bar
 * @package MongoDbVirtualCollectionsTest\Concrete\Object
 */
class Bar extends AbstractObject
{
    /**
     * @return array
     */
    public function getAssetSchema()
    {
        return array(
            'bar1',
            'bar2',
            'bar3'
        );
    }
}