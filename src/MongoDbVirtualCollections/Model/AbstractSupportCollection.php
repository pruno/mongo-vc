<?php

namespace MongoDbVirtualCollections\Model;

use Zend\Stdlib\ArrayObject;

/**
 * Class AbstractSupportCollection
 * @package MongoDbVirtualCollectionsTest\Model
 */
abstract class AbstractSupportCollection extends AbstractCollection
{
    /**
     * @var string
     */
    const CLASS_NAME_FIELD = '_class';

    /**
     * @var array
     */
    protected $virtualCollections = array();

    /**
     * @var array
     */
    protected $virtualizationGroup = array();

    /**
     * @return string
     */
    public function getClassNameField()
    {
        return static::CLASS_NAME_FIELD;
    }

    /**
     * @throws \Exception
     */
    final public function createObjectPrototype()
    {
        throw new \Exception("Support collections should never define their own prototye");
    }

    /**
     * @throws \Exception
     */
    final public function getHydrator()
    {
        throw new \Exception("Support collections should never define their own hydrator");
    }

    /**
     * @param AbstractVirtualCollection $virtualCollection
     */
    public function registerVirtualCollection(AbstractVirtualCollection $virtualCollection)
    {
        $this->virtualCollections[$virtualCollection->getAlias()] = $virtualCollection;
    }

    /**
     * @return array
     */
    public function getVirtualizationGroup()
    {
        return $this->virtualizationGroup;
    }

    /**
     * @param array $virtualizationGroup
     */
    public function setVirtualizationGroup(array $virtualizationGroup = array())
    {
        $this->virtualizationGroup = $virtualizationGroup;
    }

    /**
     * @param array $criteria
     * @return array
     */
    protected function prepareCriteria(array $criteria)
    {
        $criteria = parent::prepareCriteria($criteria);

        switch (count($this->virtualizationGroup)) {
            case 0 :
                break;

            case 1 :
                $criteria[$this->getClassNameField()] = $this->virtualizationGroup[0];
                break;

            default :
                $criteria[$this->getClassNameField()] = array(
                    '$or' => $this->virtualizationGroup
                );
        }

        return $criteria;
    }

    /**
     * @param $raw array
     * @return AbstractObject
     * @throws \Exception
     */
    public function createObjectFromRaw(array $raw)
    {
        if (!array_key_exists(static::CLASS_NAME_FIELD, $raw)) {
            throw new \Exception("Raw data is missing the className field \"".static::CLASS_NAME_FIELD."\"");
        }

        if (!array_key_exists($raw[static::CLASS_NAME_FIELD], $this->virtualCollections)) {
            throw new \Exception("Alias class {$raw[static::CLASS_NAME_FIELD]} could not be resolved into a virtual collection");
        }

        /* @var $virtualCollection AbstractVirtualCollection */
        $virtualCollection = $this->virtualCollections[$raw[static::CLASS_NAME_FIELD]];

        return $virtualCollection->createObjectFromRaw($raw);
    }

    /**
     * @param string|\MongoId $id
     * @return null|AbstractObject
     */
    public function findById($id)
    {
        return $this->findOne(array('_id' => $id));
    }

    /**
     * @param array $criteria
     * @return AbstractObject|null
     */
    public function findOne(array $criteria = array())
    {
        $criteria = $this->prepareCriteria($criteria);

        $raw = $this->findRaw($criteria, null, 1)->current();

        if (!$raw) {
            return null;
        }

        return $this->createObjectFromRaw($raw);
    }

    /**
     * @param array $criteria
     * @param array $sort
     * @param int $limit
     * @return HydratingMongoCursor
     */
    public function find(array $criteria = array(), array $sort = null, $limit = null)
    {
        $cursor = $this->findRaw($criteria, $sort, $limit);

        return new HydratingMongoCursor($cursor, $this);
    }
}
