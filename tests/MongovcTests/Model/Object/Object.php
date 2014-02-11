<?php

namespace MongovcTests\Model\Object;

use Mongovc\Model\ObjectInterface;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class Object
 * @package MongovcTests\Model\Object
 */
class Object implements ArraySerializableInterface,
                        ObjectInterface
{
    /**
     * @var string
     */
    public $_id;

    /**
     * @var mixed
     */
    public $a;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param \MongoId|string $id
     */
    public function setId($id)
    {
        $this->_id = (string) $id;
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return array(
            '_id' => $this->_id,
            'a' => $this->a
        );
    }

    /**
     * @param array $array
     */
    public function exchangeArray(array $array)
    {
        $this->_id = isset($array['_id']) ? $array['_id'] : null;
        $this->a = isset($array['a']) ? $array['a'] : null;
    }

    /**
     * @param array $data
     */
    public function enhance(array $data)
    {
        foreach ($data as $key => $val) {
            if (in_array($key, array('_id', 'a', 'b'))) {
                $this->{$key} = $val;
            }
        }
    }
}