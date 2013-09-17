<?php

return array(
    'modules' => array(
        'MongoDbVirtualCollections',
    ),
    'mongodb_virtual_collections' => array(
        'drivers' => array(
            'driver1' => array(
                'hosts' => 'localhost:27017',
                'database' => 'MongoDbVirtualCollectionsTest',
                'options' => array(

                ),
            ),
        ),
        'collections' => array(
            'MongoDbVirtualCollectionsTest\Concrete\Collection\FooCollection' => 'driver1',
            'MongoDbVirtualCollectionsTest\Concrete\SupportCollection\SupportCollection' => 'driver1',
        ),
        'virtual_collections' => array(
            'MongoDbVirtualCollectionsTest\Concrete\VirtualCollection\FooCollection' => 'MongoDbVirtualCollectionsTest\Concrete\SupportCollection\SupportCollection',
            'MongoDbVirtualCollectionsTest\Concrete\VirtualCollection\BarCollection' => 'MongoDbVirtualCollectionsTest\Concrete\SupportCollection\SupportCollection',
        ),
    ),
);