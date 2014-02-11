<?php

namespace Mongovc\Service;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CollectionAbstractServiceFactory
 * @package Mongovc\Service
 */
class CollectionAbstractServiceFactory implements AbstractFactoryInterface
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

        return isset($config[$requestedName]) && is_string($config[$requestedName]);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @return \Mongovc\Model\AbstractCollection
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator)[$requestedName];

        $mongoDb = $serviceLocator->get($config);

        $className = "\\{$requestedName}";

        return new $className($mongoDb);
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

        foreach (array('mongovc', 'collections') as $nodeIndex) {

            if (!isset($config[$nodeIndex]) || !is_array($config[$nodeIndex])) {
                return null;
            }

            $config = $config[$nodeIndex];
        }

        return $this->config = $config;
    }
}