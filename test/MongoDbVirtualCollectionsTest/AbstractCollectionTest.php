<?php

namespace MongoDbVirtualCollectionsTest;

use MongoDbVirtualCollections\Model\AbstractCollection;
use MongoDbVirtualCollections\Model\AbstractObject;
use MongoDbVirtualCollections\Model\CollectionAbstractFactory;
use MongoDbVirtualCollections\Service\MongoDbAbstractServiceFactory;

/**
 * Class AbstractCollectionTest
 * @package MongoDbVirtualCollectionsTest
 */
abstract class AbstractCollectionTest extends AbstractTestCase
{
    /**
     * @var AbstractCollection
     */
    public $collection;

    /**
     * @return AbstractCollection
     */
    abstract public function createCollection();

    /**
     * @return AbstractCollection
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $this->collection = $this->createCollection();
        }

        return $this->collection;
    }

    public function testCollectionAbstractFactory()
    {
        $conf = $this->getCollectionConfig();
        $factory = new CollectionAbstractFactory();

        $this->getServiceLocator()->addAbstractFactory(new MongoDbAbstractServiceFactory());

        $this->assertTrue(
            $factory->canCreateServiceWithName($this->getServiceLocator(), null, $conf['className']),
            'ServiceLocator can\'t create collection throught factory'
        );

        $this->assertTrue(
            $factory->createServiceWithName($this->getServiceLocator(), null, $conf['className']) instanceof AbstractCollection,
            'ServiceLocator created service is not an instance of AbstractCollection'
        );
    }

    public function tearDown()
    {
        $this->getCollection()->getCollection()->drop();
        $this->collection = null;
        parent::tearDown();
    }

    public function testHasPrimaryField()
    {
        $className = get_class($this->getCollection());
        $this->assertTrue(
            is_string($className::PRIMARY_FIELD_NAME),
            'Collection class is missing RIMARY_FIELD_NAME const'
        );

        $this->assertTrue(
            is_string($this->getCollection()->getPrimaryFieldName()),
            'getPrimaryFieldName() not returning a valid field name'
        );
    }

    public function testGetCollectionName()
    {
        $this->assertString(
            $this->getCollection()->getCollectionName(),
            'getCollectionName() should return a non-empty string'
        );
    }

    public function testCreateIdentifier()
    {
        $this->assertTrue(
            $this->getCollection()->createIdentifier() instanceof \MongoId,
            'createIdentifier() should return an instance of \MongoId'
        );
    }

    public function testGetCollection()
    {
        $this->assertTrue(
            $this->getCollection()->getCollection() instanceof \MongoCollection,
            'getCollection() should return an instance of \MongoCollection'
        );
    }

    public function testCreateObject()
    {
        $this->assertTrue(
            $this->getCollection()->createObject() instanceof AbstractObject,
            'createObject() should return an instance of MongoDbVirtualCollections\Model\AbstractObject'
        );
    }

    public function testInsert()
    {
        // primary field casting is demanded to the driver
        try {
            $this->getCollection()->insert(array(
                'foo' => 'bar'
            ));
        } catch (\Exception $e){
            $this->fail("insert() thrown an exception with message: {$e->getMessage()}");
        }

        // Test primary field casting to identifier
        try {
            $id = new \MongoId();
            $this->getCollection()->insert(array(
                $this->getCollection()->getPrimaryFieldName() => (string) $id,
                'foo' => 'bar'
            ));
        } catch (\Exception $e){
            $this->fail("insert() with primary field casting thrown an exception with message: {$e->getMessage()}");
        }
    }

    protected function dummyInsert()
    {
        $this->getCollection()->insert(array(
            'foo' => 'bar'
        ));
    }

    /**
     * @depends testInsert
     */
    public function testCount()
    {
        $this->dummyInsert();

        $this->assertEquals(
            $this->getCollection()->count(),
            1,
            'count() doesn\'t return the expected integer'
        );

        $this->assertEquals(
            $this->getCollection()->count(array(
                'foo' => 'bar'
            )),
            1,
            'count() doesn\'t return the expected integer'
        );

        $this->assertEquals(
            $this->getCollection()->count(array(
                'foo' => 'restaurant'
            )),
            0,
            'count() doesn\'t return the expected integer'
        );
    }

    /**
     * @depends testInsert
     */
    public function testSelectRawData()
    {
        $this->dummyInsert();

        $data = $this->getCollection()->selectRawData(array())->current();
        $this->assertTrue(
            is_array($data) && $data,
            'selectRawData()->current() should return a non-empty array'
        );

        $data = $this->getCollection()->selectRawData(array(
            'foo' => 'bar'
        ))->current();

        $this->assertTrue(
            is_array($data) && $data,
            'selectRawData(array(\'foo\' => \'bar\'))->current() should return a non-empty array'
        );

        $data = $this->getCollection()->selectRawData(array(
            'bar' => 'foo'
        ))->current();

        $this->assertTrue(
            $data === null,
            'selectRawData(array(\'var\' => \'foo\'))->current() should return null'
        );
    }

    /**
     * @depends testInsert
     */
    public function testSelect()
    {
        $this->dummyInsert();

        $this->assertTrue(
            $this->getCollection()->select()->current() instanceof AbstractObject,
            '->select()->current() should return an instance of AbstractObject'
        );
    }

    /**
     * @depends testInsert
     */
    public function testSelectOne()
    {
        $this->dummyInsert();

        $this->getCollection()->insert(array(
            'bar' => 'foo'
        ));

        $this->assertTrue(
            $this->getCollection()->selectOne(array()) instanceof AbstractObject,
            '->selectOne() should return an instance of AbstractObject'
        );

        $this->assertTrue(
            $this->getCollection()->selectOne(array('bar' => 'foo')) instanceof AbstractObject,
            '->selectOne() should return an instance of AbstractObject'
        );

        $this->assertNull(
            $this->getCollection()->selectOne(array('nonExistingField' => 1)),
            '->selectOne() should return an instance of AbstractObject'
        );
    }

    /**
     * @depends testInsert
     * @depends testCount
     */
    public function testUpdate()
    {
        $this->dummyInsert();

        try {
            $this->getCollection()->update(array(
                'foo' => 'bar'
            ), array(
                'bar' => 'foo'
            ));
        } catch (\Exception $e){
            $this->fail("update() thrown an exception with message: {$e->getMessage()}");
        }

        $this->assertEquals(
            $this->getCollection()->count(array(
                'bar' => 'foo'
            )),
            1,
            'count() after update() doesn\'t return the expected integer'
        );
    }

    /**
     * @depends testInsert
     * @depends testCount
     */
    public function testDelete()
    {
        $this->dummyInsert();

        $this->getCollection()->delete();

        $this->assertEquals(
            $this->getCollection()->count(),
            0,
            'count() after delete() doesn\'t return the expected integer'
        );

        $this->dummyInsert();

        $this->getCollection()->delete(array(
            'foo' => 'bar'
        ));

        $this->assertEquals(
            $this->getCollection()->count(),
            0,
            'count() after delete(array(\'foo\' => \'bar\')) doesn\'t return the expected integer'
        );

        $this->dummyInsert();

        $this->getCollection()->delete(array(
            'bar' => 'foo'
        ));

        $this->assertNotEquals(
            $this->getCollection()->count(),
            0,
            'count() after delete(array(\'bar\' => \'foo\')) doesn\'t return the expected integer'
        );
    }

    /**
     * @depends testInsert
     */
    public function testHydratingMongoCursor()
    {
        $this->dummyInsert();
        $this->dummyInsert();
        $this->dummyInsert();

        $typeTested = false;
        $c = 0;

        $hydratingCursor = $this->getCollection()->select();

        foreach ($hydratingCursor as $object) {
            ++$c;
            if (!$typeTested) {
                $this->assertTrue(
                    $object instanceof AbstractObject,
                    'iterate over select() should return an instance of AbstractObject'
                );
                $typeTested = true;
            }
        }

        $this->assertEquals(
            $hydratingCursor->count(),
            $c,
            'Unexpected hydrating cursor count() value'
        );
    }

    /**
     * @depends testInsert
     * @depends testSelectRawData
     */
    public function testGetById()
    {
        $this->dummyInsert();

        $data = $this->getCollection()->selectRawData(array())->current();

        $object = $this->getCollection()->getById((string) $data['_id']);

        $this->assertTrue($object instanceof AbstractObject, 'getById() should return an instance of MongoDbVirtualCollections\Model\AbstractObject');

        $this->assertEquals((string) $data['_id'], $object->offsetGet('_id'), "_id field does not match with the requested one");
    }
}
