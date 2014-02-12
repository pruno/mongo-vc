<?php

namespace Mongovc\Model;

use Mongovc\Hydrator\ArraySerializable;
use Mongovc\Hydrator\Strategy\MongoIdStrategy;
use MongovcTests\Model\Object\Foo;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
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
    protected $mongoCollection;

    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var ObjectInterface
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
        $this->mongoCollection = $mongoDb->selectCollection($this->collectionName);
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->getMongoCollection()->getName();
    }

    /**
     * @return \MongoCollection
     */
    public function getMongoCollection()
    {
        return $this->mongoCollection;
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
     * @return ObjectInterface
     */
    abstract protected function createObjectPrototype();

    /**
     * @return ObjectInterface
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
     * @return ObjectInterface
     */
    public function createObjectFromRaw(array $data)
    {
        return $this->getHydrator()->hydrate(
            $this->undoSet($data),
            $this->createObject()
        );
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
     * @return \MongoId|array
     */
    public function prepareIdentifier($id)
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
        if (array_key_exists('_id', $set)) {
            if ($set['_id'] === null) {
                unset($set['_id']);
            } else {
                $set['_id'] = $this->createIdentifier($set['_id']);
            }
        }

        return $set;
    }

    /**
     * @param array $set
     * @return array
     */
    protected function undoSet(array $set)
    {
        return $set;
    }

    /**
     * @param array $set
     * @param array $options
     * @return \MongoId
     */
    public function insert(array $set, array $options = array())
    {
        $tmp = $this->prepareSet($set);
        $this->getMongoCollection()->insert($tmp, $options);

        return (string) $tmp['_id'];
    }

    /**
     * @param ObjectInterface $object
     * @param array $options
     */
    public function insertObject(ObjectInterface $object, array $options = array())
    {
        $set = $this->getHydrator()->extract($object);
        $set['_id'] = $this->insert($set, $options);
        $this->getHydrator()->hydrate($set, $object);
    }

    /**
     * @param array $criteria
     * @param array $set
     * @param array $options
     * @return bool
     */
    public function update(array $criteria, array $set, array $options = array())
    {
        $set = $this->prepareSet($set);

        $this->getMongoCollection()->update($this->prepareCriteria($criteria), $set, $options);

        return isset($set['_id']) ? (string) $set['_id'] : null;
    }

    /**
     * @param ObjectInterface $object
     * @param array $options
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function updateObject(ObjectInterface $object, array $options = array())
    {
        if (!$object->getMongoId()) {
            if (isset($options['upsert']) && $options['upsert'] === true) {
                $object->setMongoId($this->createIdentifier());
            } else {
                throw new \InvalidArgumentException("\$object must provide a non-empty id if upsert is not enabled");
            }
        }

        $set = $this->getHydrator()->extract($object);
        $set['_id'] = $this->update(array('_id' => $object->getMongoId()), $set, $options);
        $this->getHydrator()->hydrate($set, $object);
    }

    /** @param array $set
     * @param array $options
     * @return array|bool
     */
    public function save(array $set, array $options = array())
    {
        $set = $this->prepareSet($set);
        $this->getMongoCollection()->save($set, $options);

        return isset($set['_id']) ? (string) $set['_id'] : null;
    }

    /**
     * @param ObjectInterface $object
     * @param array $options
     * @return bool
     */
    public function saveObject(ObjectInterface $object, array $options = array())
    {
        $set = $this->getHydrator()->extract($object);
        $set['_id'] = $this->save($set, $options);
        $this->getHydrator()->hydrate($set, $object);
    }

    /**
     * @param array $criteria
     * @param array $options
     * @return mixed
     */
    public function remove(array $criteria = array(), array $options = array())
    {
        return $this->getMongoCollection()->remove(
            $this->prepareCriteria($criteria),
            $options
        );
    }

    /**
     * @param ObjectInterface $object
     * @param array $options
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function removeObject(ObjectInterface $object, array $options = array())
    {
        if (!$object->getMongoId()) {
            throw new \InvalidArgumentException("\$object must provide a non-empty id if upsert is not enabled");
        }

        return $this->remove(
            array('_id' => $object->getMongoId()),
            $options
        );
    }

    /**
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = array())
    {
        return $this->getMongoCollection()->count(
            $this->prepareCriteria($criteria)
        );
    }

    /**
     * @param array $criteria
     * @param array $sort
     * @param null $limit
     * @return \MongoCursor
     */
    public function find(array $criteria = array(), array $sort = array(), $limit = null)
    {
        $cursor = $this->getMongoCollection()->find(
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
    public function findObjects(array $criteria = array(), array $sort = array(), $limit = null)
    {
        $cursor = $this->find($criteria, $sort, $limit);

        return $this->getHydratingMongoCursor($cursor);
    }

    /**
     * @param array $criteria
     * @return array|null
     */
    public function findOne(array $criteria = array())
    {
        return $this->getMongoCollection()->findOne(
            $this->prepareCriteria($criteria)
        );
    }

    /**
     * @param array $criteria
     * @return ObjectInterface|null
     */
    public function findObject(array $criteria = array())
    {
        $raw = $this->findOne($criteria);

        return $raw ? $this->getHydrator()->hydrate($this->undoSet($raw), $this->createObject()) : null;
    }

    /**
     * @param string|\MongoId $id
     * @return array|null
     */
    public function findById($id)
    {
        return $this->findOne(array(
            '_id' => $id
        ));
    }

    /**
     * @param string|\MongoId $id
     * @return ObjectInterface|null
     */
    public function findObjectById($id)
    {
        return $this->findObject(array(
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
        return $this->getMongoCollection()->distinct(
            $fieldName,
            $this->prepareCriteria($criteria)
        );
    }
}