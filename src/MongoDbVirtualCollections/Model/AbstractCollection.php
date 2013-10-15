<?php

namespace MongoDbVirtualCollections\Model;

use MongoDbVirtualCollections\Model\HydratingMongoCursor;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class AbstractCollection
 * @package MongoDbVirtualCollectionsTest\Model
 */
abstract class AbstractCollection implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var string
     */
    const PRIMARY_FIELD_NAME = '_id';

    /**
     * @var \MongoCollection
     */
    protected $mongoCollection;

    /**
     * @var AbstractObject
     */
    protected $objectPrototype;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param \MongoDB $mongoDb
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, \MongoDB $mongoDb)
    {
        $this->setServiceLocator($serviceLocator);
        $this->mongoCollection = $mongoDb->selectCollection($this->getCollectionName());
    }

    /**
     * @return string
     */
    abstract public function getCollectionName();

    /**
     * @return \MongoCollection
     */
    public function getCollection()
    {
        return $this->mongoCollection;
    }

    /**
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return static::PRIMARY_FIELD_NAME;
    }

    /**
     * @return ArraySerializable
     */
    protected function getHydratorDefinition()
    {
        return new ArraySerializable();
    }

    /**
     * @return array
     */
    abstract public function getAssetSchema();

    /**
     * @return HydratorInterface
     */
    protected function getHydrator()
    {
        if ($this->hydrator === null) {
            $this->hydrator = $this->getHydratorDefinition();
        }

        return $this->hydrator;
    }

    /**
     * @param \MongoCursor $cursor
     * @return HydratingMongoCursor
     */
    protected function getHydratingMongoCursor(\MongoCursor $cursor)
    {
        return new HydratingMongoCursor(
            $cursor,
            $this->getHydrator(),
            $this->getObjectPrototype()
        );
    }

    /**
     * @param mixed $id
     * @return \MongoId
     */
    public function createIdentifier($id = null)
    {
        return new \MongoId($id);
    }

    /**
     * @param array $criteria
     * @return array
     */
    protected function prepareCriteria(array $criteria)
    {
        if (
            array_key_exists($this->getPrimaryFieldName(), $criteria)
            && !($criteria[$this->getPrimaryFieldName()] instanceof \MongoId)
        ) {
            $criteria[$this->getPrimaryFieldName()] = $this->createIdentifier($criteria[$this->getPrimaryFieldName()]);
        }

        return $criteria;
    }

    /**
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = array())
    {
        return $this->getCollection()->count(
            $this->prepareCriteria($criteria)
        );
    }

    /**
     * @param array $criteria
     * @param array $sort
     * @param null $limit
     * @return \MongoCursor
     */
    public function selectRawData(array $criteria = array(), array $sort = null, $limit = null)
    {
        $cursor = $this->getCollection()->find(
            $this->prepareCriteria($criteria)
        );

        if ($sort) {
            $cursor = $cursor->sort($sort);
        }

        if ($limit) {
            $cursor = $cursor->limit($limit);
        }

        $cursor->next();

        return $cursor;
    }

    /**
     * @param array $criteria
     * @param array $sort
     * @param null $limit
     * @return HydratingMongoCursor
     */
    public function select(array $criteria = array(), array $sort = null, $limit = null)
    {
        return $this->getHydratingMongoCursor(
            $this->selectRawData($criteria, $sort, $limit)
        );
    }

    /**
     * @param array $criteria
     * @return AbstractObject|null
     */
    public function selectOne(array $criteria = array())
    {
        $raw = $this->selectRawData($criteria);
        if (!$raw->count()) {
            return null;
        }

        $object = clone $this->getObjectPrototype();
        $this->getHydrator()->hydrate($raw->current(), $object);

        return $object;
    }

    /**
     * @param array $set
     * @return array|bool
     */
    public function insert(array $set)
    {
        return $this->getCollection()->insert($set);
    }

    /**
     * @param array $criteria
     * @param array $set
     * @param array $options
     * @return boolean
     */
    public function update(array $criteria, array $set, array $options = array())
    {
        return $this->getCollection()->update(
            $this->prepareCriteria($criteria),
            $set,
            $options
        );
    }

    /**
     * @param array $criteria
     * @param array $options
     * @return mixed
     */
    public function delete(array $criteria = array(), array $options = array())
    {
        return $this->getCollection()->remove(
            $this->prepareCriteria($criteria),
            $options
        );
    }

    /**
     * @return AbstractObject
     */
    abstract protected function createObjectPrototype();

    /**
     * @return AbstractObject
     */
    public function createObject()
    {
        return clone $this->getObjectPrototype();
    }

    /**
     * @return AbstractObject
     */
    protected function getObjectPrototype()
    {
        if ($this->objectPrototype === null) {
            $this->objectPrototype = $this->createObjectPrototype();
        }

        return $this->objectPrototype;
    }

    /**
     * @param string|\MongoId $id
     * @return AbstractObject|null
     */
    public function getById($id)
    {
        return $this->selectOne(array(
            $this->getPrimaryFieldName() => $id
        ));
    }
}