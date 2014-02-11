<?php

namespace MongovcTests;

use MongovcTests\Model\Collection\BarCollection;
use MongovcTests\Model\Collection\BazCollection;
use MongovcTests\Model\Collection\FooCollection;
use MongovcTests\Model\Collection\SupportCollection;

/**
 * Class VirtualizationGroupTest
 * @package MongovcTests
 */
class VirtualizationGroupTest extends AbstractTestCase
{
    /**
     * @var SupportCollection
     */
    protected $collection;

    /**
     * @var SupportCollection
     */
    protected $virtualizationGroup;

    /**
     * @var FooCollection
     */
    protected $fooCollection;

    /**
     * @var BarCollection
     */
    protected $barCollection;

    /**
     * @var BazCollection
     */
    protected $bazCollection;

    public function setUp()
    {
        parent::setUp();

        $this->collection = new SupportCollection($this->driver);
        $this->virtualizationGroup = $this->collection->createVirtualizationGroup(array(FooCollection::ALIAS, BarCollection::ALIAS));
        $this->fooCollection = new FooCollection($this->collection);
        $this->barCollection = new BarCollection($this->collection);
        $this->bazCollection = new BazCollection($this->collection);
    }

    public function tearDown()
    {
        $this->collection->getMongoCollection()->drop();
        $this->collection = null;
        $this->virtualizationGroup = null;
        $this->fooCollection = null;
        $this->barCollection = null;
        $this->bazCollection = null;

        parent::tearDown();
    }

    public function testCreateVirtualizationGroup()
    {
        $this->assertTrue($this->virtualizationGroup instanceof SupportCollection);
    }

    public function testIsVirtualizationGroup()
    {
        $this->assertFalse($this->collection->isVirtualizationGroup());
        $this->assertTrue($this->virtualizationGroup->isVirtualizationGroup());
    }

    public function testGetIgnoredVirtualCollection()
    {
        $this->assertNull($this->virtualizationGroup->getRegisteredVirtualCollection(BazCollection::ALIAS));
    }

    public function testPrepareCriteria()
    {
        $this->fooCollection->insert(array(
            'foo' => 'bar'
        ));

        $this->barCollection->insert(array(
            'bar' => 'baz'
        ));

        $this->bazCollection->insert(array(
            'baz' => 'foo'
        ));

        $this->assertEquals($this->virtualizationGroup->count(), 2);
        $this->assertEquals($this->virtualizationGroup->count(array('foo' => 'bar')), 1);
        $this->assertEquals($this->virtualizationGroup->count(array('baz' => 'foo')), 0);
    }
}