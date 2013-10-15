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
    const ASSET_CLASS_NAME_FIELD_NAME = '_class';

    /**
     * @return string
     */
    public function getAssetClassNameFieldName()
    {
        return static::ASSET_CLASS_NAME_FIELD_NAME;
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
    final public function getAssetSchema()
    {
        throw new \Exception("Support collections should never define their own asset schema");
    }

    /**
     * @param string|\MongoId $id
     * @return AbstractObject|null
     * @deprecated Use getById
     */
    public function get($id)
    {
        return $this->getById($id);
    }

    /**
     * @param string|\MongoId $id
     * @return null|object
     * @throws \Exception
     */
    public function getById($id)
    {
        $raw = $this->selectRawData(
            array($this->getPrimaryFieldName() => ($id instanceof \MongoId) ? $id : $this->createIdentifier($id)),
            null,
            1
        )->current();

        if (!$raw) {
            return null;
        }

        if (!array_key_exists($this->getAssetClassNameFieldName(), $raw)) {
            throw new \Exception("Raw data is missing the className field \"".$this->getAssetClassNameFieldName()."\"");
        }

        /* @var $concreteCollection AbstractVirtualCollection */
        $concreteCollection = $this->getServiceLocator()->get($raw[$this->getAssetClassNameFieldName()]);

        return $concreteCollection->createAssetFromRawData($raw);
    }
}
