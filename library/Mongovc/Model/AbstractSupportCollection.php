<?php

namespace Mongovc\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Stdlib\ArrayObject;
use \Closure;
use \InvalidArgumentException;
use \RuntimeException;

/**
 * Class AbstractSupportCollection
 * @package MongovcTest\Model
 */
abstract class AbstractSupportCollection extends AbstractCollection implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

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
     * @param string $alias
     * @param AbstractVirtualCollection|FactoryInterface|Closure|string $virtualCollection
     * @throws InvalidArgumentException
     */
    public function registerVirtualCollection($alias, $virtualCollection)
    {
        if (
            !$virtualCollection instanceof AbstractVirtualCollection
            && !$virtualCollection instanceof FactoryInterface
            && !$virtualCollection instanceof Closure
            && (
                !is_string($virtualCollection)
                || !$virtualCollection
            )
        ) {
            throw new InvalidArgumentException(
                "\$virtualCollection should either be an instance of AbstractVirtualCollection, FactoryInterface, Closure or a non empty string"
            );
        }

        $this->virtualCollections[$alias] = $virtualCollection;
    }

    /**
     * @param string $alias
     * @return AbstractVirtualCollection
     * @throws RuntimeException
     */
    public function getRegisteredVirtualCollection($alias)
    {
        if (!array_key_exists($alias, $this->virtualCollections)) {
            return null;
        }

        if ($this->virtualCollections[$alias] instanceof AbstractVirtualCollection) {
            return $this->virtualCollections[$alias];
        } elseif ($this->virtualCollections[$alias] instanceof FactoryInterface) {
            $virtualCollection = $this->virtualCollections[$alias] = $this->virtualCollections[$alias]->createService($this->getServiceLocator());
        } elseif ($this->virtualCollections[$alias] instanceof Closure) {
            $virtualCollection = $this->virtualCollections[$alias] = call_user_func($this->virtualCollections[$alias]);
        } elseif (is_string($this->virtualCollections[$alias]) && $this->virtualCollections[$alias]) {
            $virtualCollection = $this->getServiceLocator()->get($this->virtualCollections[$alias]);
        } else {
            throw new RuntimeException("Inconsistent alias type");
        }

        if (!$virtualCollection instanceof AbstractVirtualCollection) {
            throw new RuntimeException("getRegisteredVirtualCollection was unable to fetch or create an instance of AbstractVirtualCollection for alias '{$alias}'");
        }

        return $virtualCollection;
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

        if ($this->virtualizationGroup) {
            $criteria[$this->getClassNameField()] = array(
                '$in' => $this->virtualizationGroup
            );
        }

        return $criteria;
    }

    /**
     * @param $raw array
     * @return AbstractObject
     * @throws RuntimeException
     */
    public function createObjectFromRaw(array $raw)
    {
        if (!array_key_exists(static::CLASS_NAME_FIELD, $raw)) {
            throw new RuntimeException("Raw data is missing the className field \"".static::CLASS_NAME_FIELD."\"");
        }

        if (!array_key_exists($raw[static::CLASS_NAME_FIELD], $this->virtualCollections)) {
            throw new RuntimeException("Alias {$raw[static::CLASS_NAME_FIELD]} could not be resolved into a virtual collection");
        }

        $virtualCollection = $this->getRegisteredVirtualCollection($raw[static::CLASS_NAME_FIELD]);

        if (!$virtualCollection instanceof AbstractVirtualCollection) {
            throw new RuntimeException("Alias {$raw[static::CLASS_NAME_FIELD]} could not be resolved into a virtual collection");
        }

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
