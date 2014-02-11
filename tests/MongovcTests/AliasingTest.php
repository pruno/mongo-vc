<?php

namespace MongovcTests;

use MongovcTests\Model\Collection\BarCollection;
use MongovcTests\Model\Collection\FooCollection;
use MongovcTests\Model\Collection\SupportCollection;
use MongovcTests\Model\Service\FooCollectionServiceFactory;

/**
 * Class Aliasingtest
 * @package MongovcTests
 */
class Aliasingtest extends AbstractTestCase
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

    public function testAliasingByInstance()
    {
        $this->assertEquals($this->fooCollection, $this->collection->getRegisteredVirtualCollection(FooCollection::ALIAS));
        $this->assertEquals($this->barCollection, $this->collection->getRegisteredVirtualCollection(BarCollection::ALIAS));
    }

    public function testAliasingByFactoryInterface()
    {
        $serviceManager = $this->createServiceManager();
        $serviceManager->setService(FooCollectionServiceFactory::SUPPORT_COLLECTION_SM_ALIAS, $this->collection);

        $this->collection->setServiceLocator($serviceManager);

        $factory = new FooCollectionServiceFactory($serviceManager);

        $this->collection->registerVirtualCollection(FooCollection::ALIAS, $factory);

        $this->assertTrue($this->collection->getRegisteredVirtualCollection(FooCollection::ALIAS) instanceof FooCollection);
    }

    public function testAliasingByClosure()
    {
        $supportCollection = $this->collection;

        $this->collection->registerVirtualCollection(FooCollection::ALIAS, function() use ($supportCollection){
            return new FooCollection($supportCollection);
        });

        $this->assertTrue($supportCollection->getRegisteredVirtualCollection(FooCollection::ALIAS) instanceof FooCollection);
    }

    public function testAliasingByServiceManagerAlias()
    {
        $smAlias = 'testSMAlias';

        $serviceManager = $this->createServiceManager();
        $serviceManager->setService($smAlias, $this->fooCollection);

        $this->collection->setServiceLocator($serviceManager);

        $this->collection->registerVirtualCollection(FooCollection::ALIAS, $smAlias);

        $this->assertTrue($this->collection->getRegisteredVirtualCollection(FooCollection::ALIAS) instanceof FooCollection);
    }

    public function testInvalidAliasRegistration()
    {
        $hasThrownAnException = false;

        try {
            $this->collection->registerVirtualCollection(FooCollection::ALIAS, 1);
        } catch (\Exception $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);
    }

    public function testInconsistentAliasRegistration()
    {
        $hasThrownAnException = false;

        try {
            $this->collection->testInconsistentAliasType(FooCollection::ALIAS);
        } catch (\Exception $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);
    }

    public function testInvalidAliasResolution()
    {
        $hasThrownAnException = false;

        try {
            $this->collection->testInvalidAliasResolution(FooCollection::ALIAS);
        } catch (\Exception $e) {
            $hasThrownAnException = true;
        }

        $this->assertTrue($hasThrownAnException);
    }
}