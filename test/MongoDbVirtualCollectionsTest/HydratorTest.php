<?php

namespace MongoDbVirtualCollectionsTest;

use MongoDbVirtualCollections\Hydrator\Strategy\MongoBinDataStrategy;
use MongoDbVirtualCollections\Hydrator\Strategy\MongoDateStrategy;
use MongoDbVirtualCollections\Hydrator\Strategy\MongoIdStrategy;
use MongoDbVirtualCollections\Hydrator\Strategy\MongoInt32Strategy;
use MongoDbVirtualCollections\Hydrator\Strategy\MongoInt64Strategy;
use MongoDbVirtualCollections\Hydrator\Strategy\MongoMaxKeyStrategy;
use MongoDbVirtualCollections\Hydrator\Strategy\MongoMinKeyStrategy;
use MongoDbVirtualCollections\Hydrator\Strategy\MongoTimestampStrategy;
use MongoDbVirtualCollections\Model\AbstractCollection;
use MongoDbVirtualCollectionsTest\Concrete\Collection\FooCollection;
use MongoDbVirtualCollectionsTest\Concrete\Object\Foo;
use Zend\Math\Rand;
use Zend\Stdlib\Hydrator\ArraySerializable;

/**
 * Class HydratorTest
 * @package MongoDbVirtualCollectionsTest
 */
class HydratorTest extends AbstractTestCase
{
    /**
     * @var string
     */
    const TEST_KEY = 'foo1';

    /**
     * @var AbstractCollection
     */
    protected $collection;

    /**
     * @return FooCollection
     */
    protected function getCollection()
    {
        if ($this->collection === null) {
            $this->collection = new FooCollection($this->getDriver());
        }

        return $this->collection;
    }

    /**
     * @return Foo
     */
    protected function createObject()
    {
        return $this->getCollection()->createObject();
    }

    /**
     * @return ArraySerializable
     */
    protected function createHydrator()
    {
        return new ArraySerializable();
    }

    /**
     * @param Foo $object
     * @param object $expectedObjectType
     * @param ArraySerializable $hydrator
     * @param string $testKey
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function extractDriverType($object, $hydrator, $expectedObjectType, $testKey = self::TEST_KEY)
    {
        if (!$object->offsetExists($testKey)) {
            throw new \InvalidArgumentException("Invalid test composition: object test key {$testKey} is missing");
        }

        $data = $hydrator->extract($object);

        $this->assertTrue(
            array_key_exists($testKey, $data),
            "Data array is missing the '{$testKey}' index "
        );

        $this->assertTrue(
            $data[$testKey] instanceof $expectedObjectType,
            "Data array '{$testKey}' field should be an instance of ".get_class($expectedObjectType)
        );

        return $data;
    }

    /**
     * @param array $data
     * @param Foo $object
     * @param ArraySerializable $hydrator
     * @param string $expectedVarType
     * @param string $testKey
     * @return Foo
     * @throws \InvalidArgumentException
     */
    protected function hydrateDriverType($data, $object, $hydrator, $expectedVarType, $testKey = 'foo1')
    {
        if (!array_key_exists($testKey, $data)) {
            throw new \InvalidArgumentException("Invalid test composition: data test key {$testKey} is missing");
        }

        $hydrator->hydrate($data, $object);

        $this->assertTrue(
            $object->offsetExists($testKey),
            "Object is missing the '{$testKey}' index "
        );

        $this->assertTrue(
            gettype($object->offsetGet($testKey)) == $expectedVarType,
            "Object '{$testKey}' attribute should be of type {$expectedVarType}: ".gettype($object->offsetGet($testKey))." given"
        );

        return $object;
    }

    public function testMongoBinDataStrategy()
    {
        $object = $this->createObject();
        $object->{self::TEST_KEY} = Rand::getBytes(500);

        $hydrator = $this->createHydrator();
        $hydrator->addStrategy(self::TEST_KEY, new MongoBinDataStrategy());

        $data = $this->extractDriverType($object, $hydrator, new \MongoBinData('', \MongoBinData::CUSTOM));
        $object2 = $this->createObject();
        $this->hydrateDriverType($data, $object2, $hydrator, 'string');

        $this->assertEquals(
            $object->{self::TEST_KEY},
            $object2->{self::TEST_KEY},
            "Extracting then Hydrating the same data should return an equal variable"
        );
    }

    public function testMongoDateStrategy()
    {
        $object = $this->createObject();
        $object->{self::TEST_KEY} = date('Y-m-d H:i:s');

        $hydrator = $this->createHydrator();
        $hydrator->addStrategy(self::TEST_KEY, new MongoDateStrategy('Y-m-d H:i:s'));

        $data = $this->extractDriverType($object, $hydrator, new \MongoDate());
        $object2 = $this->createObject();
        $this->hydrateDriverType($data, $object2, $hydrator, 'string');

        $this->assertEquals(
            $object->{self::TEST_KEY},
            $object2->{self::TEST_KEY},
            "Extracting then Hydrating the same data should return an equal variable"
        );
    }

    public function testMongoIdStrategy()
    {
        $object = $this->createObject();
        $object->{self::TEST_KEY} = (string) (new \MongoId());

        $hydrator = $this->createHydrator();
        $hydrator->addStrategy(self::TEST_KEY, new MongoIdStrategy());

        $data = $this->extractDriverType($object, $hydrator, new \MongoId());
        $object2 = $this->createObject();
        $this->hydrateDriverType($data, $object2, $hydrator, 'string');

        $this->assertEquals(
            $object->{self::TEST_KEY},
            $object2->{self::TEST_KEY},
            "Extracting then Hydrating the same data should return an equal variable"
        );
    }

    public function testMongoInt32Strategy()
    {
        $object = $this->createObject();
        $object->{self::TEST_KEY} = (string) Rand::getInteger('0', '2147483647');

        $hydrator = $this->createHydrator();
        $hydrator->addStrategy(self::TEST_KEY, new MongoInt32Strategy());

        $data = $this->extractDriverType($object, $hydrator, new \MongoInt32('0'));
        $object2 = $this->createObject();
        $this->hydrateDriverType($data, $object2, $hydrator, 'string');

        $this->assertEquals(
            $object->{self::TEST_KEY},
            $object2->{self::TEST_KEY},
            "Extracting then Hydrating the same data should return an equal variable"
        );
    }

    public function testMongoInt64Strategy()
    {
        $object = $this->createObject();
        $object->{self::TEST_KEY} = (string) Rand::getInteger('0', '9223372036854775807');

        $hydrator = $this->createHydrator();
        $hydrator->addStrategy(self::TEST_KEY, new MongoInt64Strategy());

        $data = $this->extractDriverType($object, $hydrator, new \MongoInt64('0'));
        $object2 = $this->createObject();
        $this->hydrateDriverType($data, $object2, $hydrator, 'string');

        $this->assertEquals(
            $object->{self::TEST_KEY},
            $object2->{self::TEST_KEY},
            "Extracting then Hydrating the same data should return an equal variable"
        );
    }

    public function testMongoMaxKeyStrategy()
    {
        $object = $this->createObject();
        $object->{self::TEST_KEY} = null;

        $hydrator = $this->createHydrator();
        $hydrator->addStrategy(self::TEST_KEY, new MongoMaxKeyStrategy());

        $this->extractDriverType($object, $hydrator, new \MongoMaxKey());

        $data = array(self::TEST_KEY => Rand::getString(20));
        $object2 = $this->createObject();

        $hydrator->hydrate($data, $object2);

        $this->assertEquals(
            $data[self::TEST_KEY],
            $object2->{self::TEST_KEY},
            "Hydrating should not modify data"
        );
    }

    public function testMongoMinKeyStrategy()
    {
        $object = $this->createObject();
        $object->{self::TEST_KEY} = null;

        $hydrator = $this->createHydrator();
        $hydrator->addStrategy(self::TEST_KEY, new MongoMinKeyStrategy());

        $this->extractDriverType($object, $hydrator, new \MongoMinKey());

        $data = array(self::TEST_KEY => Rand::getString(20));
        $object2 = $this->createObject();

        $hydrator->hydrate($data, $object2);

        $this->assertEquals(
            $data[self::TEST_KEY],
            $object2->{self::TEST_KEY},
            "Hydrating should not modify data"
        );
    }

    public function testMongoTimestampStrategy()
    {
        $object = $this->createObject();
        $object->{self::TEST_KEY} = time();

        $hydrator = $this->createHydrator();
        $hydrator->addStrategy(self::TEST_KEY, new MongoTimestampStrategy());

        $data = $this->extractDriverType($object, $hydrator, new \MongoTimestamp());
        $object2 = $this->createObject();
        $this->hydrateDriverType($data, $object2, $hydrator, 'integer');

        $this->assertEquals(
            $object->{self::TEST_KEY},
            $object2->{self::TEST_KEY},
            "Extracting then Hydrating the same data should return an equal variable"
        );
    }
}