<?php

namespace MongoDbVirtualCollections\Model;

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
     * @param string|\MongoId $id
     * @return null|Object
     * @throws \Exception
     */
    public function findById($id)
    {
        $raw = $this->findRaw(
            array('_id' => ($id instanceof \MongoId) ? $id : $this->createIdentifier($id)),
            null,
            1
        )->current();

        if (!$raw) {
            return null;
        }

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
}
