<?php

namespace MongovcTests\Model\Service;

use MongovcTests\Model\Collection\FooCollection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FooCollectionServiceFactory
 * @package MongovcTests
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
        /* @var $supportCollection \MongovcTests\Model\Collection\SupportCollection */
        $supportCollection = $serviceLocator->get(self::SUPPORT_COLLECTION_SM_ALIAS);

        $fooCollection = new FooCollection($supportCollection);

        return $fooCollection;
    }
}