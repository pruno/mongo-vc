<?php

namespace MongovcTests;

use Mongovc\Service\MongoDbAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Class MongoDbAbstractServiceFactoryTest
 * @package MongovcTests
 */
class MongoDbAbstractServiceFactoryTest extends AbstractTestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var MongoDbAbstractServiceFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->serviceManager = $this->createServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->factory = new MongoDbAbstractServiceFactory();
    }

    public function tearDown()
    {
        $this->factory = null;
        $this->serviceManager = null;

        parent::tearDown();
    }

    public function testCanCreateServiceWithName()
    {
        $this->assertFalse($this->factory->canCreateServiceWithName($this->serviceManager, null, '__DRIVER_ALIAS__'));

        $this->serviceManager->setService('Config', array());

        $this->assertFalse($this->factory->canCreateServiceWithName($this->serviceManager, null, '__DRIVER_ALIAS__'));

        $smConfig = array(
            'mongovc' => array(
                'drivers' => array(
                    '__DRIVER_ALIAS__' => array(
                        'hosts' => '_',
                        'database' => '_',
                    ),
                ),
            ),
        );

        $this->serviceManager->setService('Config', $smConfig);

        $this->assertFalse($this->factory->canCreateServiceWithName($this->serviceManager, null, '__WRONG_DRIVER_ALIAS__'));

        $this->assertTrue($this->factory->canCreateServiceWithName($this->serviceManager, null, '__DRIVER_ALIAS__'));
    }

    /**
     * @depends testCanCreateServiceWithName
     */
    public function testCreateServiceWithName()
    {
        $smConfig = array(
            'mongovc' => array(
                'drivers' => array(
                    '__DRIVER_ALIAS__' => $this->getConfig()['driver']
                ),
            ),
        );

        $this->serviceManager->setService('Config', $smConfig);

        $this->assertTrue($this->factory->createServiceWithName($this->serviceManager, null, '__DRIVER_ALIAS__') instanceof \MongoDB);
    }
}