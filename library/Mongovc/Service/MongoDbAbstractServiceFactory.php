<?php

namespace Mongovc\Service;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MongoClientAbstractServiceFactory
 * @package Application\Service
 */
class MongoDbAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @return boolean
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator);
        if (empty($config)) {
            return false;
        }

        return (
            isset($config[$requestedName])
            && is_array($config[$requestedName])
            && !empty($config[$requestedName])
            && isset($config[$requestedName]['database'])
            && is_string($config[$requestedName]['database'])
            && !empty($config[$requestedName]['database'])
        );
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @return \MongoClient
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator)[$requestedName];
        $hosts = array_key_exists('hosts', $config) ? $config['hosts'] : 'localhot:27017';
        $credential = array_key_exists('username', $config) && array_key_exists('password', $config) ? "{$config['username']}:{$config['password']}@" : null;
        $options = array_key_exists('options', $config) && is_array($config['options']) ? $config['options'] : array();
        $client = new \MongoClient("mongodb://{$credential}{$hosts}", $options);
        return $client->selectDB($config['database']);
    }

    /**
     * Get mongo configuration, if any
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$serviceLocator->has('Config')) {
            $this->config = array();
            return $this->config;
        }

        $config = $serviceLocator->get('Config');
        if (!isset($config['mongovc'])
            || !is_array($config['mongovc'])
        ) {
            $this->config = array();
            return $this->config;
        }

        $config = $config['mongovc'];
        if (!isset($config['drivers'])
            || !is_array($config['drivers'])
        ) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config['drivers'];
        return $this->config;
    }
}