<?php

namespace MongovcTests;

use Mongovc\Hydrator\ArraySerializable;
use Mongovc\Model\AbstractObject;
use Mongovc\Model\HydratingMongoCursor;
use MongovcTests\Model\Collection\TestCollection;
use MongovcTests\Model\Object\Foo;
use MongovcTests\Model\Object\Object;
use Zend\Math\Rand;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class CollectionTest
 * @package MongovcTests
 */
class CollectionTest extends AbstractTestCase
{
    /**
     * @var TestCollection
     */
    protected $collection;

    public function setUp()
    {
        parent::setUp();

        $this->collection = new TestCollection($this->driver);
    }

    public function tearDown()
    {
        $this->collection = null;

        parent::tearDown();
    }

    public function testGetCollectionName()
    {
        $this->assertString($this->collection->getCollectionName());
    }

    public function testgetCollection()
    {
        $this->assertTrue($this->collection->getMongoCollection() instanceof \MongoCollection);
    }

    public function testCreateObject()
    {
        $this->assertTrue($this->collection->createObject() instanceof AbstractObject);
        $this->assertTrue($this->collection->createObject() instanceof Foo);
        $this->assertNull($this->collection->createObject()->getId());
    }

    public function testCreateObjectFromRaw()
    {
        $raw = array(
            'a' => Rand::getString(10)
        );

        /* @var $object \MongovcTests\Model\Object\Foo */
        $object = $this->collection->createObjectFromRaw($raw);

        $this->assertTrue($object instanceof Foo);
        $this->assertEquals($object->a, $raw['a']);
    }

    public function testGetHydrator()
    {
        $this->assertTrue($this->collection->getHydrator() instanceof HydratorInterface);
        $this->assertTrue($this->collection->getHydrator() instanceof ArraySerializable);
        $this->assertTrue($this->collection->getHydrator()->hasStrategy('_id'));
    }

    public function testCreateIdentifier()
    {
        $mongoId = new \MongoId();

        $this->assertTrue($this->collection->createIdentifier($mongoId) instanceof \MongoId);
        $this->assertEquals($mongoId, $this->collection->createIdentifier($mongoId));
        $this->assertTrue($this->collection->createIdentifier() instanceof \MongoId);
    }

    public function testPrepareIdentifier()
    {
        $mongoId = new \MongoId();

        $this->assertEquals($mongoId, $this->collection->prepareIdentifier($mongoId));

        $identifier = $this->collection->prepareIdentifier(array($mongoId));

        $this->assertTrue(is_array($identifier));
        $this->assertTrue(count($identifier) == 1);
        $this->assertTrue($identifier[0] instanceof \MongoId);
        $this->assertEquals((string) $mongoId, (string) $identifier[0]);
    }

    public function testInsert()
    {
        $set = array('foo' => 'bar');
        $this->collection->insert($set);

        $this->assertEquals($this->collection->getMongoCollection()->count(), 1);

        $data = $this->collection->getMongoCollection()->findOne();

        $this->assertArrayHasKey('foo', $data);
        $this->assertEquals($data['foo'], 'bar');

        $this->collection->getMongoCollection()->drop();


        $_id = new \MongoId();

        $set = array('_id' => $_id, 'foo' => 'bar');
        $id = $this->collection->insert($set);

        $this->assertEquals($_id, $id);
        $this->assertEquals($this->collection->getMongoCollection()->count(), 1);

        $data = $this->collection->getMongoCollection()->findOne();
        if (!$data) {
            $this->fail("insert() failed to store data");
        }

        $this->assertArrayHasKey('_id', $data);
        $this->assertEquals($data['_id'], $_id);
        $this->assertArrayHasKey('foo', $data);
        $this->assertEquals($data['foo'], 'bar');

        $this->collection->getMongoCollection()->drop();

        $set = array('_id' => null, 'foo' => 'bar');
        $this->collection->insert($set);

        $this->assertEquals($this->collection->getMongoCollection()->count(), 1);

        $data = $this->collection->getMongoCollection()->findOne();
        if (!$data) {
            $this->fail("insert() failed to store data");
        }

        $this->assertArrayHasKey('_id', $data);
        $this->assertTrue($data['_id'] instanceof \MongoId);
        $this->assertArrayHasKey('foo', $data);
        $this->assertEquals($data['foo'], 'bar');
    }

    public function testInsertObject()
    {
        $object = new Object();

        $this->collection->insertObject($object);

        $this->assertNotEmpty($object->getId());
        $this->assertEquals($this->collection->getMongoCollection()->count(), 1);

        $this->collection->getMongoCollection()->drop();


        $_id = new \MongoId();

        $object = new Object();
        $object->setId($_id);

        $this->collection->insertObject($object);

        $this->assertEquals($object->getId(), (string) $_id);
        $this->assertEquals($this->collection->getMongoCollection()->count(), 1);

        $this->collection->getMongoCollection()->drop();


        $_id = new \MongoId();

        $object = new Object();
        $object->setId((string) $_id);

        $this->collection->insertObject($object);

        $this->assertEquals($object->getId(), (string) $_id);
        $this->assertEquals($this->collection->getMongoCollection()->count(), 1);
    }

    public function testUpdate()
    {
        $this->collection->getMongoCollection()->insert(array('foo' => 'foo'));
        $this->collection->getMongoCollection()->insert(array('foo' => 'bar'));

        $this->collection->update(array('foo' => 'bar'), array('bar' => 'baz'));

        $this->assertEquals($this->collection->getMongoCollection()->count(), 2);
        $this->assertEquals($this->collection->getMongoCollection()->count(array('foo' => 'foo')), 1);
        $this->assertEquals($this->collection->getMongoCollection()->count(array('bar' => 'baz')), 1);
        $this->assertEquals($this->collection->getMongoCollection()->count(array('foo' => 'bar')), 0);

        $this->collection->getMongoCollection()->drop();

        $this->collection->getMongoCollection()->insert(array('foo' => 'foo'));
        $this->collection->getMongoCollection()->insert(array('foo' => 'bar'));
    }

    /**
     * @depends testInsertObject
     */
    public function testUpdateObject()
    {
        $object = new Object();

        $this->collection->insertObject($object);

        $object->a = Rand::getString(10);

        $objectCpy = clone $object;

        $this->collection->updateObject($object);

        $this->assertEquals($object->a, $objectCpy->a);
        $this->assertEquals($object->_id, $objectCpy->_id);

        $objectNull = new Object();
        $objectNull->a = Rand::getString(10);

        $hasThrownAnException = false;

        try {
            $this->collection->updateObject($objectNull);
        } catch (\InvalidArgumentException $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);

        $this->collection->updateObject($objectNull, array('upsert' => true));

        $this->assertString($objectNull->getId());
    }

    public function testSave()
    {
        $id = $this->collection->save(array('foo' => 'bar'));

        $this->assertNotNull($id);
        $this->assertEquals($this->collection->getMongoCollection()->count(), 1);

        $id2 = $this->collection->save(array('_id' => $id, 'foo' => 'bar2'));

        $this->assertEquals($id, $id2);
        $this->assertEquals($this->collection->getMongoCollection()->count(), 1);

        $this->collection->getMongoCollection()->save(array('_id' => $id, 'bar' => 'foo'));

        $this->assertEquals($this->collection->count(array('bar' => 'foo')), 1);
        $this->assertEquals($this->collection->count(array('foo' => 'bar')), 0);
    }

    /**
     * @depends testInsertObject
     */
    public function testSaveObject()
    {
        $object = new Object();

        $this->collection->saveObject($object);

        $this->assertEquals($this->collection->getMongoCollection()->count(), 1);

        $object->a = Rand::getString(10);

        $objectCpy = clone $object;

        $this->collection->saveObject($object);

        $this->assertEquals($object->getId(), $objectCpy->getId());
        $this->assertEquals($this->collection->getMongoCollection()->count(), 1);
    }

    public function testRemove()
    {
        $this->collection->getMongoCollection()->insert(array('foo' => 'foo'));
        $this->collection->getMongoCollection()->insert(array('foo' => 'bar'));
        $this->collection->getMongoCollection()->insert(array('foo' => 'baz'));

        $this->collection->remove(array());

        $this->assertEquals($this->collection->getMongoCollection()->count(), 0);

        $this->collection->getMongoCollection()->insert(array('foo' => 'foo'));
        $this->collection->getMongoCollection()->insert(array('foo' => 'bar'));
        $this->collection->getMongoCollection()->insert(array('foo' => 'baz'));

        $this->collection->remove(array('foo' => 'foo'));

        $this->assertEquals($this->collection->getMongoCollection()->count(), 2);
    }

    /**
     * @depends testInsertObject
     */
    public function testRemoveObject()
    {
        $object = new Object();

        $this->collection->insertObject($object);

        $this->collection->removeObject($object);

        $this->assertEquals($this->collection->getMongoCollection()->count(), 0);


        $objectNull = new Object();

        try {

            $this->collection->removeObject($objectNull);

            $this->fail("removeObject() should fail with non-stored objects if \$options['upsert'] !== true");

        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testCount()
    {
        $this->assertEquals($this->collection->count(), 0);

        $this->collection->getMongoCollection()->insert(array('foo' => 'foo'));

        $this->assertEquals($this->collection->count(), 1);

        $this->collection->getMongoCollection()->insert(array('foo' => 'bar'));

        $this->assertEquals($this->collection->count(), 2);

        $this->assertEquals($this->collection->count(array('foo' => 'foo')), 1);

        $this->assertEquals($this->collection->count(array('bar' => 'foo')), 0);
    }

    public function testFind()
    {
        $cursor = $this->collection->find();

        $this->assertTrue($cursor instanceof \MongoCursor);
        $this->assertEquals($cursor->count(), 0);

        $this->collection->getMongoCollection()->insert(array('foo' => 'bar', 'baz' => 1));
        $this->collection->getMongoCollection()->insert(array('foo' => 'bar', 'baz' => 0));
        $this->collection->getMongoCollection()->insert(array('foo' => 'bar2'));
        $this->collection->getMongoCollection()->insert(array('foo' => 'bar3'));

        $this->assertEquals($this->collection->find(array('foo' => 'bar'))->count(), 2);
        $this->assertEquals($this->collection->find(array('foo' => 'bar'), array('baz' => 1))->current()['baz'], 0);

        $cursor = $this->collection->find(array(), array(), 2);
        $count = 0;

        foreach ($cursor as $document) {
            ++$count;
        }

        $this->assertEquals($count, 2);
    }

    /**
     * @depends testInsertObject
     */
    public function testFindObjects()
    {
        $this->assertTrue($this->collection->findObjects() instanceof HydratingMongoCursor);
        $this->assertEquals($this->collection->findObjects()->count(), 0);

        $object = new Object();
        $object->a = Rand::getString(10);

        $this->collection->insertObject($object);

        $cursor = $this->collection->findObjects(array());

        $this->assertTrue($cursor instanceof HydratingMongoCursor);
        $this->assertEquals($cursor->count(), 1);
    }

    public function testFindOne()
    {
        $this->assertNull($this->collection->findOne());

        $set = array('foo' => 'bar');

        $this->collection->getMongoCollection()->insert($set);

        $data = $this->collection->findOne(array('_id' => $set['_id']));

        $this->assertTrue(is_array($data));
        $this->assertEquals($data['_id'], $set['_id']);
    }

    /**
     * @depends testInsertObject
     */
    public function testFindObject()
    {
        $this->assertNull($this->collection->findObject());

        $object = new Object();

        $this->collection->insertObject($object);

        $object2 = $this->collection->findObject();

        $this->assertTrue($object2 instanceof Foo);
        $this->assertEquals($object->getId(), $object2->getId());
    }

    public function testFindById()
    {
        $this->collection->getMongoCollection()->insert(array('foo' => 'bar'));

        $id = new \MongoId();
        $id = (string) $id;

        $result = $this->collection->findById($id);
        $result2 = $this->collection->findOne(array('_id' => $id));

        $this->assertEquals($result, $result2);

        $set = array('foo' => 'bar');

        $this->collection->getMongoCollection()->insert($set);

        $result = $this->collection->findById($set['_id']);
        $result2 = $this->collection->findOne(array('_id' => $set['_id']));

        $this->assertEquals($result, $result2);
    }

    public function testFindObjectById()
    {
        $this->collection->getMongoCollection()->insert(array('foo' => 'bar'));

        $id = new \MongoId();
        $id = (string) $id;

        $result = $this->collection->findObjectById($id);
        $result2 = $this->collection->findObject(array('_id' => $id));

        $this->assertEquals($result, $result2);

        $set = array('foo' => 'bar');

        $this->collection->getMongoCollection()->insert($set);

        $result = $this->collection->findObjectById($set['_id']);
        $result2 = $this->collection->findObject(array('_id' => $set['_id']));

        $this->assertEquals($result, $result2);
    }

    public function testDistinct()
    {
        $result = $this->collection->distinct('foo');

        $this->assertTrue(is_array($result));
        $this->assertEmpty($result);

        $this->collection->getMongoCollection()->insert(array('foo' => 'foo'));
        $this->collection->getMongoCollection()->insert(array('foo' => 'bar'));
        $this->collection->getMongoCollection()->insert(array('bar' => 'foo'));

        $this->assertEmpty($this->collection->distinct('baz'));

        $result = $this->collection->distinct('foo');

        $this->assertEquals(count($result), 2);
        $this->assertTrue(in_array('foo', $result));
        $this->assertTrue(in_array('bar', $result));

        $result = $this->collection->distinct('foo', array('foo' => 'bar'));

        $this->assertEquals(count($result), 1);
        $this->assertEquals($result[0], 'bar');
    }
}