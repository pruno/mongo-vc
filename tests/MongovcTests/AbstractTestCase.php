<?php

namespace MongovcTests;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AbstractTestCase
 * @package MongovcTests
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var \MongoDB
     */
    protected $driver;

    public function setUp()
    {
        parent::setUp();

        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('Mongo extension is required to run tests');
        }

        $this->driver = $this->createDriver();
    }

    public function tearDown()
    {
        if ($this->driver instanceof \MongoDB) {
            $this->driver->drop();
        }

        $this->driver = null;

        parent::tearDown();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return Bootstrap::getConfig()['mongovc_tests'];
    }

    /**
     * @return \MongoDB
     */
    public function createDriver()
    {
        $config = $this->getConfig()['driver'];

        $credential = array_key_exists('username', $config) && array_key_exists('password', $config) ? "{$config['username']}:{$config['password']}@" : null;
        $client = new \MongoClient("mongodb://{$credential}{$config['hosts']}", isset($config['options']) ? $config['options'] : array());

        return $client->selectDB($config['database']);
    }

    /**
     * @param mixed $config
     * @return ServiceManager
     */
    public function createServiceManager($config = null)
    {
        $serviceManager = new ServiceManager(new ServiceManagerConfig());

        if ($config) {
            $serviceManager->setService('ApplicationConfig', $config);
            $serviceManager->setService('Config', 'ApplicationConfig');
        }

        return $serviceManager;
    }

    /**
     * @param mixed $subject
     * @param string $message
     */
    public function assertString($subject, $message = '')
    {
        $this->assertTrue(
            is_string($subject) && $subject,
            $message
        );
    }
}