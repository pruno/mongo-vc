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
        $this->supportCollection->registerVirtualCollection($this->getAlias(), $this);
    }

    /**
     * @return \MongoCollection
     */
    public function getMongoCollection()
    {
        return $this->getSupportCollection()->getMongoCollection();
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
    public function getSupportCollection()
    {
        return $this->supportCollection;
    }

    /**
     * @return string
     */
    public function getClassNameField()
    {
        return $this->supportCollection->getClassNameField();
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

    /**
     * @param array $set
     * @return array
     */
    protected function undoSet(array $set)
    {
        if (array_key_exists($this->getClassNameField(), $set)) {
            unset($set[$this->getClassNameField()]);
        }

        return $set;
    }
}