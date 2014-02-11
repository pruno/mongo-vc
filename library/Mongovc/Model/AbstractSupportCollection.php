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
    protected $groupAliases = array();

    /**
     * @var AbstractSupportCollection
     */
    protected $parentSupportCollection;

    /**
     * @return bool
     */
    public function isVirtualizationGroup()
    {
        return $this->parentSupportCollection ? true : false;
    }

    /**
     * @param array $aliases
     * @return AbstractSupportCollection
     */
    public function createVirtualizationGroup(array $aliases)
    {
        $aliases = array_unique($aliases);
        $aliases = array_combine($aliases, $aliases);

        $virtualizaionGroup = clone $this;
        $virtualizaionGroup->parentSupportCollection = $this;
        $virtualizaionGroup->virtualCollections = array();
        $virtualizaionGroup->groupAliases = $aliases;

        return $virtualizaionGroup;
    }

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
    final protected function createObjectPrototype()
    {
        throw new \Exception("Support collections should never define their own prototye");
    }

    /**
     * @throws \Exception
     */
    final protected function createHydrator()
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
        if ($this->groupAliases && !array_key_exists($alias, $this->groupAliases)) {
            return null;
        }

        if (!array_key_exists($alias, $this->virtualCollections)) {
            return $this->parentSupportCollection ? $this->parentSupportCollection->getRegisteredVirtualCollection($alias) : null;
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
     * @param array $criteria
     * @return array
     */
    protected function prepareCriteria(array $criteria)
    {
        $criteria = parent::prepareCriteria($criteria);

        if ($this->groupAliases) {
            $criteria[$this->getClassNameField()] = array(
                '$in' => $this->groupAliases
            );
        }

        return $criteria;
    }

    /**
     * @param array $set
     * @return void
     * @throws \Exception
     */
    protected function prepareSet(array $set = array())
    {
        throw new \Exception("Support collection are not desined to perform write operation");
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

        $virtualCollection = $this->getRegisteredVirtualCollection($raw[static::CLASS_NAME_FIELD]);

        if (!$virtualCollection instanceof AbstractVirtualCollection) {
            throw new RuntimeException("Alias {$raw[static::CLASS_NAME_FIELD]} could not be resolved into a virtual collection");
        }

        return $virtualCollection->createObjectFromRaw($raw);
    }

    /**
     * @param array $criteria
     * @return AbstractObject|null
     */
    public function findObject(array $criteria = array())
    {
        $criteria = $this->prepareCriteria($criteria);

        $raw = $this->findOne($criteria, array());

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
    public function findObjects(array $criteria = array(), array $sort = array(), $limit = null)
    {
        $cursor = $this->find($criteria, $sort, $limit);

        return new HydratingMongoCursor($cursor, $this);
    }
}
