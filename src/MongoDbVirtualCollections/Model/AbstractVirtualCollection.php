<?php

namespace MongoDbVirtualCollections\Model;

use MongoDbVirtualCollections\Model\AbstractCollection;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AbstractVirtualCollection
 * @package MongoDbVirtualCollectionsTest\Model
 */
abstract class AbstractVirtualCollection extends AbstractCollection
{
    /**
     * @var AbstractSupportCollection
     */
    protected $supportCollection;

    /**
     * @param AbstractSupportCollection $supportCollection
     */
    public function __construct(AbstractSupportCollection $supportCollection)
    {
        $this->supportCollection = $supportCollection;
        $this->setServiceLocator($supportCollection->getServiceLocator());
    }

    /**
     * @return \MongoCollection
     */
    final public function getCollection()
    {
        return $this->getSupportCollection()->getCollection();
    }

    /**
     * @return AbstractSupportCollection
     */
    final public function getSupportCollection()
    {
        return $this->supportCollection;
    }

    /**
     * @return string
     */
    final public function getCollectionName()
    {
        return $this->getSupportCollection()->getCollectionName();
    }

    /**
     * @return string
     */
    final public function getPrimaryFieldName()
    {
        return $this->getSupportCollection()->getPrimaryFieldName();
    }

    /**
     * @return string
     */
    public function getAssetClassName()
    {
        return get_class($this->getObjectPrototype());
    }

    /**
     * @return string
     */
    public function getCollectionClassName()
    {
        return get_class($this);
    }

    /**
     * @return string
     */
    final public function getAssetClassNameFieldName()
    {
        return $this->getSupportCollection()->getAssetClassNameFieldName();
    }

    /**
     * @param array $criteria
     * @return array
     */
    protected function prepareCriteria(array $criteria)
    {
        $criteria = parent::prepareCriteria($criteria);
        $criteria[$this->getAssetClassNameFieldName()] = $this->getCollectionClassName();

        return $criteria;
    }

    /**
     * @param array $data
     * @return object
     */
    public function createAssetFromRawData(array $data)
    {
        return $this->getHydrator()->hydrate(
            $data,
            $this->getObjectPrototype()
        );
    }

    /**
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = array())
    {
        return $this->getSupportCollection()->count(
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
        return $this->getSupportCollection()->selectRawData(
            $this->prepareCriteria($criteria),
            $sort,
            $limit
        );
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
            $this->selectRawData(
                $this->prepareCriteria($criteria),
                $sort,
                $limit
            )
        );
    }

    /**
     * @param array $set
     * @return array|bool
     */
    public function insert(array $set)
    {
        return $this->getSupportCollection()->insert(
            $this->prepareCriteria($set)
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
        return $this->getSupportCollection()->update(
            $this->prepareCriteria($criteria),
            $this->prepareCriteria($set),
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
        return $this->getSupportCollection()->delete(
            $this->prepareCriteria($criteria),
            $options
        );
    }
}