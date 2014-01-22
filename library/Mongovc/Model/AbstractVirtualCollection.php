<?php

namespace Mongovc\Model;

use Mongovc\Model\AbstractCollection;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AbstractVirtualCollection
 * @package MongovcTest\Model
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
    const ALIAS = null;

    /**
     * @param AbstractSupportCollection $supportCollection
     */
    public function __construct(AbstractSupportCollection $supportCollection)
    {
        $this->supportCollection = $supportCollection;
        $this->collection = $this->supportCollection->getCollection();
        $this->supportCollection->registerVirtualCollection($this->getAlias(), $this);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return static::ALIAS ? static::ALIAS : __CLASS__;
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
        $set = parent::prepareSet($set);

        $set[$this->getClassNameField()] = $this->getAlias();

        return $set;
    }
}