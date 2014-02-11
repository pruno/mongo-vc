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

        return isset($config[$requestedName]);
    }

    /**
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
     * @param  ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$serviceLocator->has('Config')) {
            return null;
        }

        $config = $serviceLocator->get('Config');

        foreach (array('mongovc', 'drivers') as $nodeIndex) {

            if (!isset($config[$nodeIndex]) || !is_array($config[$nodeIndex])) {
                return null;
            }

            $config = $config[$nodeIndex];
        }

        return $this->config = $config;
    }
}