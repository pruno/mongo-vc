<?php

namespace MongoDbVirtualCollections\Model;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class VirtualCollectionAbstractFactory implements AbstractFactoryInterface
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
            && class_exists("\\{$requestedName}")
            && (
                is_callable($config[$requestedName])
                || (is_string($config[$requestedName]) && $serviceLocator->has($config[$requestedName]))
            )
        );
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @return \MongoDbVirtualCollections\Model\AbstractCollection
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator)[$requestedName];

        if (is_callable($config)) {
            $supportCollection =  $config($serviceLocator);
        } else {
            $supportCollection = $serviceLocator->get($config);
        }

        $className = "\\{$requestedName}";

        return new $className($supportCollection);
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
        if (!isset($config['mongodb_virtual_collections'])
            || !is_array($config['mongodb_virtual_collections'])
        ) {
            $this->config = array();
            return $this->config;
        }

        $config = $config['mongodb_virtual_collections'];
        if (!isset($config['virtual_collections'])
            || !is_array($config['virtual_collections'])
        ) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config['virtual_collections'];
        return $this->config;
    }
}