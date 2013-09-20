<?php

namespace MongoDbVirtualCollections\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use ArrayAccess;
use Countable;

/**
 * Class AbstractAsset
 * @package MongoDbVirtualCollectionsTest\Model
 */
abstract class AbstractObject implements ServiceLocatorAwareInterface,
                                         Countable,
                                         ArrayAccess
{
    use ServiceLocatorAwareTrait;

    /**
     * @var AbstractCollection
     */
    protected $collection;

    /**
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param AbstractCollection $collection
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, AbstractCollection $collection)
    {
        $this->setServiceLocator($serviceLocator);
        $this->collection = $collection;
        $this->initialize();
    }

    /**
     * @return array
     */
    protected function getAssetSchema()
    {
        return $this->getCollection()->getAssetSchema();
    }

    protected function initialize()
    {
        if ($this->isInitialized) {
            return;
        }

        foreach ($this->getAssetSchema() as $fieldName) {
            $this->data[$fieldName] = null;
        }

        $this->data[$this->getCollection()->getPrimaryFieldName()] = null;

        $this->isInitialized = true;
    }

    /**
     * @return AbstractCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return array
     */
    protected function getPrimaryCriteria()
    {
        $primary = $this->getCollection()->getPrimaryFieldName();
        return array($primary, $this->getCollection()->createIdentifier($this->data[$primary]));
    }

    /**
     * @return bool
     */
    public function objectExistsInDatabase()
    {
        return isset($this->data[$this->getCollection()->getPrimaryFieldName()]);
    }

    /**
     * @return array|bool
     */
    public function save()
    {
        if ($this->objectExistsInDatabase()) {
            $this->getCollection()->update(
                $this->data,
                $this->getPrimaryCriteria()
            );
        } else {
            $this->data['_id'] = $this->getCollection()->createIdentifier();
            $this->getCollection()->insert($this->data);
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function delete()
    {
        if (!$this->objectExistsInDatabase()) {
            throw new \Exception("The asset must exists in database to be deleted");
        }

        return $this->getCollection()->delete(
            $this->getPrimaryCriteria()
        );
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @param string $offset
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \InvalidArgumentException('Not a valid field in this object: ' . $offset);
        }

        return $this->data[$offset];
    }


    /**
     * @param string $offset
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return AbstractObject
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->offsetExists($offset)) {
            throw new \InvalidArgumentException('Not a valid field in this object: ' . $offset);
        }

        $this->data[$offset] = $value;
        return $this;
    }

    /**
     * @param  string $offset
     * @return AbstractObject
     * @throws \InvalidArgumentException
     */
    public function offsetUnset($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \InvalidArgumentException('Not a valid field in this object: ' . $offset);
        }

        $this->data[$offset] = null;
        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = $this->data;
        if (isset($data[$this->getCollection()->getPrimaryFieldName()])) {
            $data[$this->getCollection()->getPrimaryFieldName()] = (string) $data[$this->getCollection()->getPrimaryFieldName()];
        }
        return $data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate(array $data)
    {
        foreach ($this->data as &$value) {
            $value = null;
        }

        return $this->enhance($data);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function enhance(array $data)
    {
        foreach ($data as $field => $value) {
            if ($this->offsetExists($field)) {
                $this->offsetSet($field, $value);
            }
        }

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            throw new \InvalidArgumentException('Not a valid field in this object: ' . $name);
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @param string $name
     * @return void
     */
    public function __unset($name)
    {
        $this->offsetUnset($name);
    }
}