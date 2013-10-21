<?php

namespace MongoDbVirtualCollectionsTest;

use MongoDbVirtualCollections\Hydrator\Strategy\MongoIdStrategy;
use MongoDbVirtualCollectionsTest\Concrete\Collection\FooCollection;
use MongoDbVirtualCollectionsTest\Concrete\Object\Foo;
use Zend\Stdlib\Hydrator\ArraySerializable;

/**
 * Class HydratorTest
 * @package MongoDbVirtualCollectionsTest
 */
class HydratorTest extends AbstractTestCase
{
    /**
     * @return ArraySerializable
     */
    protected function createHydrator()
    {
        return new ArraySerializable();
    }

    /**
     * @return FooCollection
     */
    protected function createCollection()
    {
        return new FooCollection($this->getDriver());
    }

    /**
     * @return Foo
     */
    protected function createObject()
    {
        return new Foo($this->createCollection());
    }

    public function testMongoIdStrategy()
    {
        $hydrator = $this->createHydrator();
        $hydrator->addStrategy('_id', new MongoIdStrategy());

        $dummyIdString = (string) new \MongoId();

        $object = $this->createObject();
        $object->_id = $dummyIdString;

        $data = $hydrator->extract($object);

        $this->assertTrue(
            isset($data['_id']),
            "Data array is either missing the '_id' index or it's null"
        );

        $this->assertTrue(
            $data['_id'] instanceof \MongoId,
            "Data array '_id' field should be an instance of \\MongoId"
        );

        $this->assertEquals(
            $dummyIdString,
            (string) $data['_id'],
            "Id does't match"
        );

        $dummyId = new \MongoId();
        $object = $this->createObject();
        $data = array('_id' => $dummyId);
        $hydrator->hydrate($data, $object);

        $this->assertTrue(
            is_string($object->_id),
            "Object _id property should be a string"
        );

        $this->assertEquals(
            (string) $dummyId,
            $object->_id,
            "Id doesn't match"
        );
    }
}