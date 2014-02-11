<?php

namespace MongovcTests;

use Mongovc\Model\HydratingMongoCursor;
use MongovcTests\Model\Collection\TestCollection;
use MongovcTests\Model\Object\Foo;
use Zend\Math\Rand;

/**
 * Class HydratingMongoCursorTest
 * @package MongovcTests
 */
class HydratingMongoCursorTest extends AbstractTestCase
{
    /**
     * @var TestCollection
     */
    protected $collection;

    public function setUp()
    {
        parent::setUp();

        $this->collection = new TestCollection($this->driver);

        for ($i = 0; $i < 4; $i++) {
            $this->collection->getMongoCollection()->insert(array(
                'a' => Rand::getString(10)
            ));
        }
    }

    public function testDependencies()
    {
        $mongoCursor = $this->collection->find();

        $cursor = new HydratingMongoCursor($mongoCursor, $this->collection);

        $this->assertEquals($cursor->getCursor(), $mongoCursor);
        $this->assertEquals($cursor->getCollection(), $this->collection);
    }

    public function testIteratornterface()
    {
        $mongoCursor = $this->collection->find();

        $cursor = new HydratingMongoCursor($mongoCursor, $this->collection);

        $this->assertTrue($cursor instanceof \Iterator);
        $this->assertTrue($cursor->current() instanceof Foo);
        $this->assertTrue($cursor->valid());
        $this->assertString($cursor->key());
        $this->assertTrue(is_array($cursor->toArray()));
        $this->assertArrayHasKey(0, $cursor->toArray());
        $this->assertTrue($cursor->toArray()[0] instanceof Foo);

        $cursor->next();
        $cursor->rewind();

        $mongoCursor = $this->collection->getMongoCollection()->find(array('_NON_EXISTING_ATTRIBUTE_' => 1));

        $cursor = new HydratingMongoCursor($mongoCursor, $this->collection);

        $this->assertNull($cursor->current());
        $this->assertFalse($cursor->valid());
        $this->assertNull($cursor->key());
        $this->assertTrue(is_array($cursor->toArray()));
        $this->assertEquals(count($cursor->toArray()), 0);
    }
}