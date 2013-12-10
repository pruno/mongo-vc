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
     * @var string
     */
    const SUPPORT_COLLECTION_SM_ALIAS = 'testSupportCollection';

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return FooCollection
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $supportCollection \MongoDbVirtualCollectionsTest\Concrete\SupportCollection\SupportCollection */
        $supportCollection = $serviceLocator->get(self::SUPPORT_COLLECTION_SM_ALIAS);

        $fooCollection = new FooCollection($supportCollection);

        return $fooCollection;
    }
}