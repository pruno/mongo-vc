<?php

namespace MongoDbVirtualCollectionsTest;

use MongoDbVirtualCollectionsTest\Concrete\Service\FooCollectionServiceFactory;
use MongoDbVirtualCollectionsTest\Concrete\SupportCollection\SupportCollection;
use MongoDbVirtualCollectionsTest\Concrete\VirtualCollection\FooCollection;

/**
 * Class Aliasingtest
 * @package MongoDbVirtualCollectionsTest
 */
class Aliasingtest extends AbstractTestCase
{
    /**
     * @return SupportCollection
     */
    protected function createSupportCollection()
    {
        return new SupportCollection($this->getDriver());
    }

    public function testAliasingByInstance()
    {
        $supportCollection = $this->createSupportCollection();
        $virtualCollection = new FooCollection($supportCollection);

        $this->assertEquals(
            $virtualCollection,
            $supportCollection->getRegisteredVirtualCollection(FooCollection::ALIAS),
            "getRegisteredVirtualCollection() failed to return the registerd instance of FooCollection"
        );
    }

    public function testAliasingByFactoryInterface()
    {
        $supportCollection = $this->createSupportCollection();

        /* @var $serviceManager \Zend\ServiceManager\ServiceManager */
        $serviceManager = $this->getServiceLocator();
        $serviceManager->setService(FooCollectionServiceFactory::SUPPORT_COLLECTION_SM_ALIAS, $supportCollection);

        $supportCollection->setServiceLocator($serviceManager);

        $factory = new FooCollectionServiceFactory($serviceManager);
        $alias = FooCollection::ALIAS;

        $supportCollection->registerVirtualCollection($alias, $factory);

        $this->assertTrue(
            $supportCollection->getRegisteredVirtualCollection($alias) instanceof FooCollection,
            "getRegisteredVirtualCollection() should return an instance of FooCollection"
        );
    }

    public function testAliasingByClosure()
    {
        $supportCollection = $this->createSupportCollection();

        $alias = FooCollection::ALIAS;

        $supportCollection->registerVirtualCollection($alias, function() use ($supportCollection){
            return new FooCollection($supportCollection);
        });

        $this->assertTrue(
            $supportCollection->getRegisteredVirtualCollection($alias) instanceof FooCollection,
            "getRegisteredVirtualCollection() should return an instance of FooCollection"
        );
    }

    public function testAliasingByServiceManagerAlias()
    {
        $supportCollection = $this->createSupportCollection();
        $virtualCollection = new FooCollection($supportCollection);

        $alias = FooCollection::ALIAS;
        $smAlias = 'testSMAlias';

        /* @var $serviceManager \Zend\ServiceManager\ServiceManager */
        $serviceManager = $this->getServiceLocator();
        $serviceManager->setService($smAlias, $virtualCollection);

        $supportCollection->setServiceLocator($serviceManager);

        $supportCollection->registerVirtualCollection($alias, $smAlias);

        $this->assertTrue(
            $supportCollection->getRegisteredVirtualCollection($alias) instanceof FooCollection,
            "getRegisteredVirtualCollection() should return an instance of FooCollection"
        );
    }
}