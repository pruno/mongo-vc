<?php

namespace MongovcTests;

use Mongovc\Service\MongoDbAbstractServiceFactory;

class MongoDbAbstractServiceFactoryTest extends AbstractTestCase
{
    public function testFactoryCreateDriver()
    {
        $conf = $this->getDriverConfig();
        $factory = new MongoDbAbstractServiceFactory();

        $this->assertTrue(
            $factory->canCreateServiceWithName($this->getServiceLocator(), null, $conf['alias']),
            'ServiceLocator can\'t create mongoDb service throught factory'
        );

        $this->assertTrue(
            $factory->createServiceWithName($this->getServiceLocator(), null, $conf['alias']) instanceof \MongoDB,
            'ServiceLocator created service is not an instance of \MonggoDb'
        );
    }
}