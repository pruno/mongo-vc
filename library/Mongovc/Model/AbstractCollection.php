<?php

namespace Mongovc\Model;

use Mongovc\Hydrator\Strategy\MongoIdStrategy;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class AbstractCollection
 * @package MongovcTest\Model
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
     * @return ArraySerializable
     */
    protected function createHydrator()
    {
        $hydrator = new ArraySerializable();
        $hydrator->addStrategy('_id', new MongoIdStrategy());

        return $hydrator;
    }

    /**
     * @return ArraySerializable
     */
    public function getHydrator()
    {
        if ($this->hydrator === null) {
            $this->hydrator = $this->createHydrator();
        }

        return $this->hydrator;
    }

    /**
     * @param \MongoCursor $cursor
     * @return HydratingMongoCursor
     */
    protected function getHydratingMongoCursor(\MongoCursor $cursor)
    {
        return new HydratingMongoCursor($cursor, $this);
    }

    /**
     * @param mixed $id
     * @return \MongoId
     */
    public function createIdentifier($id = null)
    {
        return $id instanceof \MongoId ? $id : new \MongoId($id);
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
        } else {
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
     * @param array $set
     * @return array
     */
    protected function prepareSet(array $set)
    {
        if (array_key_exists('_id', $set) && $set['_id'] === null) {
            unset($set['_id']);
        }

        return $set;
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
        return $this->collection->insert(
            $this->prepareSet($set)
        );
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
            $this->prepareSet($set),
            $options
        );
    }

    /**
     * @param array $set
     * @param array $options
     * @return array|bool
     */
    public function save(array &$set, array $options = array())
    {
        // passing a referenced variable to save will fail in update the content
        $tmp = $this->prepareSet($set);
        $result = $this->getCollection()->save($tmp, $options);
        $set = $tmp;

        return $result;
    }

    /**
     * @param AbstractObject $object
     * @param array $options
     * @return boolean
     */
    public function updateObject(AbstractObject $object, $options = array())
    {
        $set = $this->getHydrator()->extract($object);

        $success = $this->save($set, $options);

        $this->getHydrator()->hydrate($set, $object);

        return $success;
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
     * @param array $data
     * @return AbstractObject
     */
    public function createObjectFromRaw(array $data)
    {
        return $this->getHydrator()->hydrate(
            $data,
            $this->createObject()
        );
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

    /**
     * @param string $fieldName
     * @param array $criteria
     * @return array
     */
    public function distinct($fieldName, $criteria = array())
    {
        return $this->getCollection()->distinct(
            $fieldName,
            $this->prepareCriteria($criteria)
        );
    }
}