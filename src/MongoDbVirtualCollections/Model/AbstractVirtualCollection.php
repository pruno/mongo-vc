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
     * @var string
     */
    protected $alias = null;

    /**
     * @param AbstractSupportCollection $supportCollection
     */
    public function __construct(AbstractSupportCollection $supportCollection)
    {
        $this->supportCollection = $supportCollection;
        $this->collection = $this->supportCollection->getCollection();
        $this->supportCollection->registerVirtualCollection($this);
    }

    /**
     * @return null|string
     */
    public function getAlias()
    {
        if ($this->alias === null) {
            $this->alias = get_class($this);
        }

        return $this->alias;
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
    final public function getClassNameField()
    {
        return $this->getSupportCollection()->getClassNameField();
    }

    /**
     * @param array $criteria
     * @return array
     */
    protected function prepareCriteria(array $criteria)
    {
        $criteria = parent::prepareCriteria($criteria);
        $criteria[$this->getClassNameField()] = $this->getAlias();

        return $criteria;
    }

    /**
     * @param array $set
     * @return array
     */
    protected function prepareSet(array $set)
    {
        $set[$this->getClassNameField()] = $this->getAlias();

        return $set;
    }

    /**
     * @param array $data
     * @return object
     */
    public function createObjectFromRaw(array $data)
    {
        return $this->getHydrator()->hydrate(
            $data,
            $this->createObject()
        );
    }
}