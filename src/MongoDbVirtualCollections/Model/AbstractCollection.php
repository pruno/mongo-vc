<?php

namespace MongoDbVirtualCollections\Model;

use MongoDbVirtualCollections\Hydrator\Strategy\MongoIdStrategy;
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
abstract class AbstractCollection
{
    /**
     * @var \MongoCollection
     */
    protected $collection;

    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var AbstractObject
     */
    protected $objectPrototype;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @param \MongoDB $mongoDb
     */
    public function __construct(\MongoDB $mongoDb)
    {
        $this->collection = $mongoDb->selectCollection($this->collectionName);
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collection->getName();
    }

    /**
     * @return \MongoCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return HydratorInterface|null
     */
    public function getHydrator()
    {
        if ($this->hydrator === null) {
            $this->hydrator = new ArraySerializable();
            $this->hydrator->addStrategy('_id', new MongoIdStrategy());
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
            $this->createObject()
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
     * @param $id
     * @return \MongoId
     */
    protected function prepareIdentifier($id)
    {
        if (is_array($id)) {
            foreach ($id as &$val) {
                $val = $this->prepareIdentifier($val);
            }
        } elseif (!$id instanceof \MongoId) {
            $id = $this->createIdentifier($id);
        }

        return $id;
    }

    /**
     * @param array $criteria
     * @return array
     */
    protected function prepareCriteria(array $criteria)
    {
        if (array_key_exists('_id', $criteria)) {
            $criteria['_id'] = $this->prepareIdentifier($criteria['_id']);
        }

        return $criteria;
    }

    /**
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = array())
    {
        return $this->collection->count(
            $this->prepareCriteria($criteria)
        );
    }

    /**
     * @param array $criteria
     * @param array $sort
     * @param null $limit
     * @return \MongoCursor
     */
    public function findRaw(array $criteria = array(), array $sort = null, $limit = null)
    {
        $cursor = $this->collection->find(
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
    public function find(array $criteria = array(), array $sort = null, $limit = null)
    {
        return $this->getHydratingMongoCursor(
            $this->findRaw($criteria, $sort, $limit)
        );
    }

    /**
     * @param array $criteria
     * @return AbstractObject|null
     */
    public function findOne(array $criteria = array())
    {
        $raw = $this->collection->findOne(
            $this->prepareCriteria($criteria)
        );

        if (!$raw) {
            return null;
        }

        $object = $this->createObject();
        $this->getHydrator()->hydrate($raw, $object);

        return $object;
    }

    /**
     * @param array $set
     * @return array|bool
     */
    public function insert(array $set)
    {
        return $this->collection->insert($set);
    }

    /**
     * @param array $criteria
     * @param array $set
     * @param array $options
     * @return boolean
     */
    public function update(array $criteria, array $set, array $options = array())
    {
        return $this->collection->update(
            $this->prepareCriteria($criteria),
            $set,
            array_merge(array('upsert' => true), $options)
        );
    }

    /**
     * @param array $criteria
     * @param array $options
     * @return mixed
     */
    public function remove(array $criteria = array(), array $options = array())
    {
        return $this->collection->remove(
            $this->prepareCriteria($criteria),
            $options
        );
    }

    /**
     * @return AbstractObject
     */
    abstract public function createObjectPrototype();

    /**
     * @return AbstractObject
     */
    public function createObject()
    {
        if ($this->objectPrototype === null) {
            $this->objectPrototype = $this->createObjectPrototype();
        }

        return clone $this->objectPrototype;
    }

    /**
     * @param string|\MongoId $id
     * @return AbstractObject|null
     */
    public function findById($id)
    {
        return $this->findOne(array(
            '_id' => $id
        ));
    }
}