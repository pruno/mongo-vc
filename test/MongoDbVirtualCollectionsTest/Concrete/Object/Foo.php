<?php

namespace MongoDbVirtualCollectionsTest\Concrete\Object;

use MongoDbVirtualCollections\Model\AbstractObject;

/**
 * Class Foo
 * @package MongoDbVirtualCollectionsTest\Concrete\Object
 */
class Foo extends AbstractObject
{
    /**
     * @return array
     */
    public function getAssetSchema()
    {
        return array(
            'foo1',
            'foo2'
        );
    }
}