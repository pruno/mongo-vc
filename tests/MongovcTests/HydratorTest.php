<?php

namespace MongovcTests;

use Mongovc\Hydrator\Strategy\MongoBinDataStrategy;
use Mongovc\Hydrator\Strategy\MongoDateStrategy;
use Mongovc\Hydrator\Strategy\MongoIdStrategy;
use Mongovc\Hydrator\Strategy\MongoInt32Strategy;
use Mongovc\Hydrator\Strategy\MongoInt64Strategy;
use Mongovc\Hydrator\Strategy\MongoMaxKeyStrategy;
use Mongovc\Hydrator\Strategy\MongoMinKeyStrategy;
use Mongovc\Hydrator\Strategy\MongoTimestampStrategy;
use MongovcTests\Model\Object\EnhancebleObject;
use MongovcTests\Model\Object\SerializableObject;
use Zend\Math\Rand;
use Mongovc\Hydrator\ArraySerializable;

/**
 * Class HydratorTest
 * @package MongovcTests
 */
class HydratorTest extends AbstractTestCase
{
    /**
     * @var string
     */
    const TEST_KEY = 'a';

    /**
     * @var ArraySerializable
     */
    protected $hydrator;

    /**
     * @var SerializableObject
     */
    protected $object1;

    /**
     * @var SerializableObject
     */
    protected $object2;

    public function setUp()
    {
        parent::setUp();

        $this->hydrator = new ArraySerializable();
        $this->object1 = new EnhancebleObject();
        $this->object2 = new EnhancebleObject();
    }

    public function tearDown()
    {
        $this->hydrator = null;
        $this->object1 = null;
        $this->object2 = null;

        parent::tearDown();
    }

    /**
     * @param object $object
     * @param object $expectedObjectType
     * @param ArraySerializable $hydrator
     * @param string $testKey
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function extractDriverType($object, $hydrator, $expectedObjectType, $testKey = self::TEST_KEY)
    {
        if (!property_exists($object, $testKey)) {
            throw new \InvalidArgumentException("Invalid test composition: object test key {$testKey} is missing");
        }

        $data = $hydrator->extract($object);

        $this->assertTrue(array_key_exists($testKey, $data));

        $this->assertTrue(
            $data[$testKey] instanceof $expectedObjectType,
            "Data array '{$testKey}' field should be an instance of ".get_class($expectedObjectType)
        );

        return $data;
    }

    /**
     * @param array $data
     * @param object $object
     * @param ArraySerializable $hydrator
     * @param string $expectedVarType
     * @param string $testKey
     * @return object
     * @throws \InvalidArgumentException
     */
    protected function hydrateDriverType($data, $object, $hydrator, $expectedVarType, $testKey = self::TEST_KEY)
    {
        if (!array_key_exists($testKey, $data)) {
            throw new \InvalidArgumentException("Invalid test composition: data test key {$testKey} is missing");
        }

        $hydrator->hydrate($data, $object);

        $this->assertTrue(property_exists($object, $testKey));

        $this->assertTrue(
            gettype($object->{$testKey}) == $expectedVarType,
            "Object '{$testKey}' attribute should be of type {$expectedVarType}: ".gettype($object->{$testKey})." given"
        );

        return $object;
    }

    public function testNonEnhancebleHydration()
    {
        $object = new SerializableObject();

        $this->hydrator->addStrategy('a', new MongoInt32Strategy());

        $int32 = New \MongoInt32('100');

        $this->hydrator->hydrate(array('a' => $int32), $object);

        $this->assertEquals($int32->value, $object->a);
    }

    public function testMongoBinDataStrategy()
    {
        $this->hydrator->addStrategy(self::TEST_KEY, new MongoBinDataStrategy());

        $this->object1->{self::TEST_KEY} = Rand::getBytes(500);

        $data = $this->extractDriverType($this->object1, $this->hydrator, new \MongoBinData('', \MongoBinData::CUSTOM));

        $this->hydrateDriverType($data, $this->object2, $this->hydrator, 'string');

        $this->assertEquals($this->object1->{self::TEST_KEY}, $this->object2->{self::TEST_KEY});
    }

    public function testMongoDateStrategy()
    {
        $this->hydrator->addStrategy(self::TEST_KEY, new MongoDateStrategy('Y-m-d H:i:s'));

        $this->object1->{self::TEST_KEY} = date('Y-m-d H:i:s');
        
        $data = $this->extractDriverType($this->object1, $this->hydrator, new \MongoDate());
        
        $this->hydrateDriverType($data, $this->object2, $this->hydrator, 'string');

        $this->assertEquals($this->object1->{self::TEST_KEY}, $this->object2->{self::TEST_KEY});
    }

    public function testMongoIdStrategy()
    {
        $this->hydrator->addStrategy(self::TEST_KEY, new MongoIdStrategy());

        $this->object1->{self::TEST_KEY} = (string) (new \MongoId());

        $data = $this->extractDriverType($this->object1, $this->hydrator, new \MongoId());

        $this->hydrateDriverType($data, $this->object2, $this->hydrator, 'string');

        $this->assertEquals($this->object1->{self::TEST_KEY}, $this->object2->{self::TEST_KEY});
    }

    public function testMongoInt32Strategy()
    {
        $this->hydrator->addStrategy(self::TEST_KEY, new MongoInt32Strategy());

        $this->object1->{self::TEST_KEY} = (string) Rand::getInteger('0', '2147483647');

        $data = $this->extractDriverType($this->object1, $this->hydrator, new \MongoInt32('0'));

        $this->hydrateDriverType($data, $this->object2, $this->hydrator, 'string');

        $this->assertEquals($this->object1->{self::TEST_KEY}, $this->object2->{self::TEST_KEY});
    }

    public function testMongoInt64Strategy()
    {
        $this->hydrator->addStrategy(self::TEST_KEY, new MongoInt64Strategy());

        $this->object1->{self::TEST_KEY} = (string) Rand::getInteger('0', '9223372036854775807');

        $data = $this->extractDriverType($this->object1, $this->hydrator, new \MongoInt64('0'));

        $this->hydrateDriverType($data, $this->object2, $this->hydrator, 'string');

        $this->assertEquals($this->object1->{self::TEST_KEY}, $this->object2->{self::TEST_KEY});
    }

    public function testMongoMaxKeyStrategy()
    {
        $this->hydrator->addStrategy(self::TEST_KEY, new MongoMaxKeyStrategy());

        $this->extractDriverType($this->object1, $this->hydrator, new \MongoMaxKey());

        $data = array(self::TEST_KEY => Rand::getString(20));

        $this->hydrator->hydrate($data, $this->object2);

        $this->assertEquals($data[self::TEST_KEY], $this->object2->{self::TEST_KEY});
    }

    public function testMongoMinKeyStrategy()
    {
        $this->hydrator->addStrategy(self::TEST_KEY, new MongoMinKeyStrategy());

        $this->extractDriverType($this->object1, $this->hydrator, new \MongoMinKey());

        $data = array(self::TEST_KEY => Rand::getString(20));

        $this->hydrator->hydrate($data, $this->object2);

        $this->assertEquals($data[self::TEST_KEY], $this->object2->{self::TEST_KEY});
    }

    public function testMongoTimestampStrategy()
    {
        $this->hydrator->addStrategy(self::TEST_KEY, new MongoTimestampStrategy());

        $this->object1->{self::TEST_KEY} = time();

        $data = $this->extractDriverType($this->object1, $this->hydrator, new \MongoTimestamp());

        $this->hydrateDriverType($data, $this->object2, $this->hydrator, 'integer');

        $this->assertEquals($this->object1->{self::TEST_KEY}, $this->object2->{self::TEST_KEY});
    }
}