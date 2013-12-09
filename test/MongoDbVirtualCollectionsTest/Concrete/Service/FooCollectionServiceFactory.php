<?php

namespace MongoDbVirtualCollectionsTest\Concrete\Service;

use MongoDbVirtualCollectionsTest\Concrete\VirtualCollection\FooCollection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FooCollectionServiceFactory
 * @package MongoDbVirtualCollectionsTest
 */
class FooCollectionServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return FooCollection
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $supportCollection \MongoDbVirtualCollectionsTest\Concrete\SupportCollection\SupportCollection */
        $supportCollection = $serviceLocator->get('testSupportCollection');

        $fooCollection = new FooCollection($supportCollection);

        return $fooCollection;
    }
}