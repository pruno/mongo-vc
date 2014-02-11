<?php

namespace MongovcTests;

use Mongovc\Model\AbstractObject;
use Mongovc\Model\HydratingMongoCursor;
use MongovcTests\Model\Collection\BarCollection;
use MongovcTests\Model\Collection\FooCollection;
use MongovcTests\Model\Collection\SupportCollection;
use MongovcTests\Model\Object\Bar;
use MongovcTests\Model\Object\Foo;
use MongovcTests\Model\Object\Object;

/**
 * Class VirtualizationTest
 * @package MongovcTests
 */
class VirtualizationTest extends AbstractTestCase
{
    /**
     * @var SupportCollection
     */
    protected $collection;

    /**
     * @var FooCollection
     */
    protected $fooCollection;

    /**
     * @var BarCollection
     */
    protected $barCollection;

    public function setUp()
    {
        parent::setUp();

        $this->collection = new SupportCollection($this->driver);
        $this->fooCollection = new FooCollection($this->collection);
        $this->barCollection = new BarCollection($this->collection);
    }

    public function tearDown()
    {
        $this->collection->getMongoCollection()->drop();
        $this->collection = null;
        $this->fooCollection = null;
        $this->barCollection = null;

        parent::tearDown();
    }

    public function testCreateObject()
    {
        $hasThrownAnException = false;

        try {
            $this->collection->createObject();
        } catch (\Exception $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);

        $this->assertTrue($this->fooCollection->createObject() instanceof Foo);
    }

    public function testCreateObjectFromRaw()
    {
        $object = $this->collection->createObjectFromRaw(array(
            'foo' => 'bar',
            $this->collection->getClassNameField() => $this->fooCollection->getAlias()
        ));

        $this->assertTrue($object instanceof Foo);

        $hasThrowAnException = false;

        try {
            $object = $this->collection->createObjectFromRaw(array('foo' => 'bar'));
        } catch (\Exception $e) {
            $hasThrowAnException = true;
        }

        $this->assertTrue($hasThrowAnException);

        $hasThrowAnException = false;

        try {
            $object = $this->collection->createObjectFromRaw(array(
                'foo' => 'bar',
                $this->collection->getClassNameField() => '__NON_EXISTING_ALIAS__'
            ));
        } catch (\Exception $e) {
            $hasThrowAnException = true;
        }

        $this->assertTrue($hasThrowAnException);

        $this->assertTrue($this->fooCollection->createObjectFromRaw(array('foo' => 'bar')) instanceof Foo);
    }

    public function testInsert()
    {
        $hasThrownAnException = false;

        try {
            $this->collection->insert(array('foo' => 'bar'));
        } catch (\Exception $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);

        $this->assertString($this->fooCollection->insert(array('foo' => 'bar')));
    }

    public function testInsertObject()
    {
        $hasThrownAnException = false;

        try {
            $object = new Object();
            $this->collection->insertObject($object);
        } catch (\Exception $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);

        $object = new Object();
        $this->fooCollection->insertObject($object);

        $this->assertNotNull($object->getId());
    }

    public function testUpdate()
    {
        $hasThrownAnException = false;

        try {
            $this->collection->update(array('foo' => 'bar'), array('foo' => 'bar2'), array('upsert' => true));
        } catch (\Exception $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);

        $this->fooCollection->update(array('foo' => 'bar'), array('foo' => 'bar2'), array('upsert' => true));

        $this->assertEquals($this->fooCollection->count(), 1);
    }

    public function testUpdateObject()
    {
        $hasThrownAnException = false;

        $object = new Foo($this->fooCollection);

        try {
            $this->collection->updateObject($object);
        } catch (\Exception $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);

        $object = new Object();
        $this->fooCollection->updateObject($object, array('upsert' => true));

        $this->assertNotNull($object->getId());
    }

    public function testFind()
    {
        $this->barCollection->insert(array(
            'bar' => 'foo'
        ));

        $this->fooCollection->insert(array(
            'foo' => 'bar'
        ));

        $this->barCollection->insert(array(
            'bar' => 'foo'
        ));

        $this->assertTrue($this->collection->find() instanceof \MongoCursor);
        $this->assertArrayHasKey('foo', $this->collection->find(array('foo' => 'bar'))->current());
        $this->assertEquals($this->collection->findObjects(array('__NON_EXISTING_PROPERTY__' => 1))->count(), 0);

        $this->assertTrue($this->fooCollection->find() instanceof \MongoCursor);
        $this->assertArrayHasKey('foo', $this->fooCollection->find(array('foo' => 'bar'))->current());
        $this->assertEquals($this->fooCollection->find(array('bar' => 'foo'))->count(), 0);
    }

    public function testFindObjects()
    {
        $this->barCollection->insert(array(
            'bar' => 'foo'
        ));

        $this->fooCollection->insert(array(
            'foo' => 'bar'
        ));

        $this->barCollection->insert(array(
            'bar' => 'foo'
        ));

        $this->assertTrue($this->collection->findObjects() instanceof HydratingMongoCursor);
        $this->assertTrue($this->collection->findObjects(array('foo' => 'bar'))->current() instanceof Foo);
        $this->assertEquals($this->collection->findObjects(array('__NON_EXISTING_PROPERTY__' => 1))->count(), 0);

        $this->assertTrue($this->fooCollection->findObjects() instanceof HydratingMongoCursor);
        $this->assertTrue($this->fooCollection->findObjects(array('foo' => 'bar'))->current() instanceof Foo);
        $this->assertEquals($this->fooCollection->findObjects(array('bar' => 'foo'))->count(), 0);
    }

    public function testFindOne()
    {
        $this->fooCollection->insert(array(
            'foo' => 'bar'
        ));

        $this->barCollection->insert(array(
            'bar' => 'foo'
        ));

        $this->assertTrue(is_array($this->collection->findOne()));
        $this->assertArrayHasKey('bar', $this->collection->findOne(array('bar' => 'foo')));
        $this->assertNull($this->collection->findOne(array('__NON_EXISTING_PROPERTY__' => 1)));

        $this->assertTrue(is_array($this->fooCollection->findOne()));
        $this->assertArrayHasKey('foo', $this->fooCollection->findOne(array('foo' => 'bar')));
        $this->assertNull($this->fooCollection->findOne(array('bar' => 'foo')));
    }

    public function testFindObject()
    {
        $this->fooCollection->insert(array(
            'foo' => 'bar'
        ));

        $this->barCollection->insert(array(
            'bar' => 'foo'
        ));

        $this->assertTrue($this->collection->findObject() instanceof AbstractObject);
        $this->assertTrue($this->collection->findObject(array('bar' => 'foo')) instanceof Bar);
        $this->assertNull($this->collection->findObject(array('__NON_EXISTING_PROPERTY__' => 1)));

        $this->assertTrue($this->fooCollection->findObject() instanceof Foo);
        $this->assertNull($this->fooCollection->findObject(array('bar' => 'foo')));
    }

    public function testFindById()
    {
        $idFoo = new \MongoId();
        $idBar = new \MongoId();

        $this->fooCollection->insert(array(
            '_id' => $idFoo,
            'foo' => 'bar'
        ));

        $this->barCollection->insert(array(
            '_id' => $idBar,
            'bar' => 'foo'
        ));

        $this->assertArrayHasKey('foo', $this->collection->findById($idFoo));

        $this->assertArrayHasKey('foo', $this->fooCollection->findById($idFoo));
        $this->assertNull($this->fooCollection->findById($idBar));
    }

    public function testFindObjectById()
    {
        $idFoo = new \MongoId();
        $idBar = new \MongoId();

        $this->fooCollection->insert(array(
            '_id' => $idFoo,
            'foo' => 'bar'
        ));

        $this->barCollection->insert(array(
            '_id' => $idBar,
            'bar' => 'foo'
        ));

        $this->assertTrue($this->collection->findObjectById($idFoo) instanceof Foo);
        $this->assertTrue($this->collection->findObjectById((string) $idFoo) instanceof Foo);

        $this->assertTrue($this->fooCollection->findObjectById($idFoo) instanceof Foo);
        $this->assertNull($this->fooCollection->findObjectById($idBar));
    }
}