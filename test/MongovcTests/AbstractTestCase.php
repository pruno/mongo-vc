<?php

namespace MongovcTests;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class AbstractTestCase
 * @package MongovcTests
 */
abstract class AbstractTestCase extends TestCase implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function setUp()
    {
        $this->setServiceLocator(Bootstrap::getServiceManager());

        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('Mongo extension is required to run tests');
        }

        if (!$this->getDriverConfig()) {
            $this->markTestSkipped('No driver configuration found');
        }
    }

    /**
     * @return array|object
     */
    public function getConfig()
    {
        return $this->getServiceLocator()->get('Config');
    }

    /**
     * @param int $index
     * @return null|array
     */
    public function getCollectionConfig($index = 0)
    {
        $config = null;
        $c = 0;
        foreach ($this->getConfig()['mongovc']['collections'] as $className => $alias) {
            if ($index == $c) {
                return array(
                    'className'     =>  $className,
                    'alias'         =>  $alias
                );
            }
            ++$c;
        }

        return null;
    }

    /**
     * @param int $index
     * @return null|array
     */
    public function getVirtualCollectionConfig($index = 0)
    {
        $config = null;
        $c = 0;
        foreach ($this->getConfig()['mongovc']['virtual_collections'] as $className => $alias) {
            if ($index == $c) {
                return array(
                    'className'     =>  $className,
                    'alias'         =>  $alias
                );
            }
            ++$c;
        }

        return null;
    }

    /**
     * @param int $index
     * @return null|array
     */
    public function getDriverConfig($index = 0)
    {
        $driver = null;
        $c = 0;
        foreach ($this->getConfig()['mongovc']['drivers'] as $alias => $driver) {
            if ($index == $c) {
                return array(
                    'alias'     =>  $alias,
                    'driver'    =>  $driver
                );
            }
            ++$c;
        }
        return null;
    }

    /**
     * @param int $index
     * @return \MongoDB|null
     */
    public function getDriver($index = 0)
    {
        if (!$config = $this->getDriverConfig($index)) {
            $this->fail("Could not find driver configuration with index {$index}");
            return null;
        }

        $config = $config['driver'];

        $credential = array_key_exists('username', $config) && array_key_exists('password', $config) ? "{$config['username']}:{$config['password']}@" : null;
        $client = new \MongoClient("mongodb://{$credential}{$config['hosts']}", $config['options']);
        return $client->selectDB($config['database']);
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