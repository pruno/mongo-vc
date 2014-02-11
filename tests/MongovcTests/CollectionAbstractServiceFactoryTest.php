<?php

namespace MongovcTests;

use Mongovc\Service\CollectionAbstractServiceFactory;
use Mongovc\Service\MongoDbAbstractServiceFactory;
use MongovcTests\Model\Collection\TestCollection;
use Zend\ServiceManager\ServiceManager;

/**
 * Class CollectionAbstractServiceFactoryTest
 * @package MongovcTests
 */
class CollectionAbstractServiceFactoryTest extends AbstractTestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var CollectionAbstractServiceFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->serviceManager = $this->createServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->addAbstractFactory(new MongoDbAbstractServiceFactory());
        $this->factory = new CollectionAbstractServiceFactory();
    }

    public function tearDown()
    {
        $this->factory = null;
        $this->serviceManager = null;

        parent::tearDown();
    }

    public function testCanCreateServiceWithName()
    {
        $FQN = 'MongovcTests\Model\Collection\TestCollection';

        $this->assertFalse($this->factory->canCreateServiceWithName($this->serviceManager, null, $FQN));

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
                    $FQN => '__DRIVER_ALIAS__',
                ),
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
        $FQN = 'MongovcTests\Model\Collection\TestCollection';

        $smConfig = array(
            'mongovc' => array(
                'drivers' => array(
                    '__DRIVER_ALIAS__' => $this->getConfig()['driver']
                ),
                'collections' => array(
                    $FQN => '__DRIVER_ALIAS__',
                ),
            ),
        );

        $this->serviceManager->setService('Config', $smConfig);

        $this->assertTrue($this->factory->createServiceWithName($this->serviceManager, null, $FQN) instanceof TestCollection);
    }
}