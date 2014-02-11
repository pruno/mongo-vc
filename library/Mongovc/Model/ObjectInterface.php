<?php

namespace Mongovc\Model;

use Zend\Stdlib\ArraySerializableInterface;

/**
 * Interface ObjectInterface
 * @package MongovcTests\Model
 */
interface ObjectInterface extends ArraySerializableInterface
{
    /**
     * @return string|\MongoId
     */
    public function getId();

    /**
     * @param string|\MongoId $id
     * @return void
     */
    public function setId($id);
}