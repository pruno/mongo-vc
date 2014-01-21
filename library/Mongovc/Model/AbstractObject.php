<?php

namespace Mongovc\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use ArrayAccess;
use Countable;

/**
 * Class AbstractAsset
 * @package MongovcTest\Model
 */
abstract class AbstractObject implements Countable,
                                         ArrayAccess
{
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
    protected $schema;

    /**
     * @var string
     */
    public $_id;

    /**
     * @param AbstractCollection $collection
     */
    public function __construct(AbstractCollection $collection)
    {
        $this->collection = $collection;
        $this->initialize();
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        if ($this->schema === null) {
            $properties = array();
            $reflection = new \ReflectionClass($this);
            foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                $properties[$property->name] = $property->name;
            }

            $this->schema = $properties;
        }

        return $this->schema;
    }

    protected function initialize()
    {
        if ($this->isInitialized) {
            return;
        }

        $this->getSchema();

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
        return array(
            '_id' => $this->collection->createIdentifier($this->_id)
        );
    }

    /**
     * @return bool
     */
    public function objectExistsInDatabase()
    {
        return isset($this->_id);
    }

    /**
     * @return void
     */
    public function save()
    {
        $this->getCollection()->updateObject($this);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function delete()
    {
        if (!$this->objectExistsInDatabase()) {
            throw new \Exception("The asset must exists in database to be deleted");
        }

        $this->collection->remove(
            $this->getPrimaryCriteria()
        );
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->schema);
    }

    /**
     * @param string $offset
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }


    /**
     * @param string $offset
     * @param mixed $value
     * @return AbstractObject
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;

        return $this;
    }

    /**
     * @param  string $offset
     * @return AbstractObject
     */
    public function offsetUnset($offset)
    {
        $this->{$offset} = null;

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->schema);
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        $data = array();

        foreach ($this->schema as $offset) {
            $data[$offset] = $this->{$offset};
        }

        return $data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate(array $data)
    {
        foreach ($this->schema as $offset) {
            $this->{$offset} = null;
        }

        return $this->enhance($data);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function enhance(array $data)
    {
        foreach ($data as $offset => $value) {
            if ($this->offsetExists($offset)) {
                $this->{$offset} = $value;
            }
        }

        return $this;
    }

    /**
     * @param $name
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __get($name)
    {
        throw new \InvalidArgumentException('Not a valid field in this object: ' . $name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __set($name, $value)
    {
        throw new \InvalidArgumentException('Not a valid field in this object: ' . $name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return false;
    }

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __unset($name)
    {
        throw new \InvalidArgumentException('Not a valid field in this object: ' . $name);
    }
}