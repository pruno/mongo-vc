<?php

namespace MongovcTests;

use Mongovc\Service\CollectionAbstractServiceFactory;
use Mongovc\Service\MongoDbAbstractServiceFactory;
use Mongovc\Service\VirtualCollectionAbstractServiceFactory;
use MongovcTests\Model\Collection\FooCollection;
use Zend\ServiceManager\ServiceManager;

/**
 * Class VirtualCollectionAbstractServiceFactoryTest
 * @package MongovcTests
 */
class VirtualCollectionAbstractServiceFactoryTest extends AbstractTestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var VirtualCollectionAbstractServiceFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->serviceManager = $this->createServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->addAbstractFactory(new MongoDbAbstractServiceFactory());
        $this->serviceManager->addAbstractFactory(new CollectionAbstractServiceFactory());
        $this->factory = new VirtualCollectionAbstractServiceFactory();
    }

    public function tearDown()
    {
        $this->factory = null;
        $this->serviceManager = null;

        parent::tearDown();
    }

    public function testCanCreateServiceWithName()
    {
        $FQN = 'MongovcTests\Model\Collection\FooCollection';

        // FIXME this generate an infite loop on service manager abstract factories calls
//        $this->assertFalse($this->factory->canCreateServiceWithName($this->serviceManager, null, $FQN));

        $this->serviceManager->setService('Config', array());

        $this->assertFalse($this->factory->canCreateServiceWithName($this->serviceManager, null, $FQN));

        $smConfig = array(
            'mongovc' => array(
                'drivers' => array(
                    '__DRIVER_ALIAS__' => array(
                        'hosts' => '_',
                        'database' => '_',
                    ),
                ),
                'collections' => array(
                    'MongovcTests\Model\Collection\SupportCollection' => '__DRIVER_ALIAS__',
                ),
                'virtual_collections' => array(
                    $FQN => 'MongovcTests\Model\Collection\SupportCollection',
                )
            ),
        );

        $this->serviceManager->setService('Config', $smConfig);

        $this->assertFalse($this->factory->canCreateServiceWithName($this->serviceManager, null, '__WRONG_FQN__'));

        $this->assertTrue($this->factory->canCreateServiceWithName($this->serviceManager, null, $FQN));
    }

    /**
     * @depends testCanCreateServiceWithName
     */
    public function testCreateServiceWithName()
    {
        $FQN = 'MongovcTests\Model\Collection\FooCollection';

        $smConfig = array(
            'mongovc' => array(
                'drivers' => array(
                    '__DRIVER_ALIAS__' => $this->getConfig()['driver']
                ),
                'collections' => array(
                    'MongovcTests\Model\Collection\SupportCollection' => '__DRIVER_ALIAS__',
                ),
                'virtual_collections' => array(
                    $FQN => 'MongovcTests\Model\Collection\SupportCollection',
                )
            ),
        );

        $this->serviceManager->setService('Config', $smConfig);

        $this->assertTrue($this->factory->createServiceWithName($this->serviceManager, null, $FQN) instanceof FooCollection);
    }
}