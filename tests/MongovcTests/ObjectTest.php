<?php

namespace MongovcTests;

use Mongovc\Model\ObjectInterface;
use MongovcTests\Model\Collection\TestCollection;
use MongovcTests\Model\Object\Foo;
use Zend\Math\Rand;

/**
 * Class ObjectTest
 * @package MongovcTests
 */
class ObjectTest extends AbstractTestCase
{
    /**
     * @var Foo
     */
    protected $object;

    public function setUp()
    {
        parent::setUp();

        $this->object = new Foo(new TestCollection($this->driver));
    }

    public function tearDown()
    {
        if ($this->object) {
            $this->object = null;
        }

        parent::tearDown();
    }

    public function testInterface()
    {
        $this->assertTrue($this->object instanceof ObjectInterface);
    }

    public function testMultipleInitialization()
    {
        $this->assertTrue($this->object->testMultipleInitialization());
    }

    public function testMagicMethods()
    {
        $hasThrownAnException = false;

        try {
            $this->object->{"_NON_EXISTSING_PROPERTY_"} = true;
        } catch (\InvalidArgumentException $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);


        $hasThrownAnException = false;

        try {
            $tmp = $this->object->{"_NON_EXISTSING_PROPERTY_"};
        } catch (\InvalidArgumentException $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);


        $hasThrownAnException = false;

        try {
            unset($this->object->{"_NON_EXISTSING_PROPERTY_"});
        } catch (\InvalidArgumentException $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);

        $this->assertFalse(isset($this->object->{"_NON_EXISTSING_PROPERTY_"}));

    }

    public function testGetterSetter()
    {
        $id = new \MongoId();

        $this->object->setMongoId($id);

        $this->assertTrue($this->object->getMongoId() === $id || $this->object->getMongoId() === (string) $id);

        $this->tearDown();
        $this->setUp();

        $this->object->setMongoId($id);

        $this->assertTrue($this->object->getMongoId() === $id || $this->object->getMongoId() === (string) $id);
    }

    public function testArrayAccess()
    {
        $this->assertTrue($this->object instanceof \ArrayAccess);
        $this->assertTrue($this->object->offsetExists('a'));
        $this->assertFalse($this->object->offsetExists('_NON_EXISTING_OFFSET_'));

        $this->object->offsetSet('a', 'foo');

        $this->assertEquals($this->object->a, 'foo');
        $this->assertEquals($this->object->offsetGet('a'), 'foo');

        $this->object->offsetUnset('a');

        $this->assertNull($this->object->a);
    }

    public function testToArray()
    {
        $this->object->a = 'foo';

        $array = $this->object->toArray();

        $this->assertTrue(is_array($array));
        $this->assertArrayHasKey('a', $array);
        $this->assertEquals($array['a'], $this->object->a);
    }

    public function testPopulate()
    {
        $array = array(
            'a' => Rand::getString(10),
            '_NON_EXISTING_PROPERTY' => Rand::getString(10)
        );

        $this->object->populate($array);

        $this->assertEquals($this->object->a, $array['a']);
        $this->assertFalse($this->object->offsetExists('_NON_EXISTING_PROPERTY'));

        $this->tearDown();
        $this->setUp();

        $array = array(
            'a' => Rand::getString(10),
        );

        $this->object->save();

        $this->object->populate($array);

        $this->assertNull($this->object->_id);
    }

    public function testExchangeArray()
    {
        $array = array(
            'a' => Rand::getString(10),
            '_NON_EXISTING_PROPERTY' => Rand::getString(10)
        );

        $objectCpy = clone $this->object;

        $this->assertEquals(
            $this->object->exchangeArray($array)->toArray(),
            $objectCpy->exchangeArray($array)->toArray()
        );
    }

    public function testGetCollection()
    {
        $collection = new TestCollection($this->driver);
        $object = new Foo($collection);

        $this->assertEquals($collection, $object->getCollection());
    }

    public function testSave()
    {
        $this->object->a = 'foo';

        try {
            $this->object->save();
        } catch (\Exception $e) {
            $this->fail("save() thrown an exception with message: {$e->getMessage()}");
        }

        $this->assertEquals($this->object->getCollection()->getMongoCollection()->count(), 1);
        $this->assertEquals($this->object->getCollection()->getMongoCollection()->findOne()['a'], 'foo');
    }

    /**
     * @depends testSave
     */
    public function testObjectExistsInDatabase()
    {
        $this->assertFalse($this->object->objectExistsInDatabase());

        $this->object->save();

        $this->assertTrue($this->object->objectExistsInDatabase());
    }

    /**
     * @depends testSave
     */
    public function testUpsert()
    {
        $this->object->a = 'foo';
        $this->object->save();

        $this->object->a = 'bar';
        $this->object->save();

        $this->assertEquals($this->object->getCollection()->getMongoCollection()->count(), 1);
        $this->assertEquals($this->object->getCollection()->getMongoCollection()->findOne()['a'], 'bar');
    }

    /**
     * @depends testSave
     */
    public function testDelete()
    {
        $hasThrownAnException = false;

        try {
            $this->object->delete();
        } catch (\Exception $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);

        $this->object->save();
        $this->object->delete();

        $this->assertEmpty($this->object->getCollection()->getMongoCollection()->count(), 0);
    }
}