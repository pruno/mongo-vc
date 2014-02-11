<?php

namespace MongovcTests\Model\Object;

use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class SerializableObject
 * @package MongovcTests\Model\Object
 */
class SerializableObject extends \StdClass implements ArraySerializableInterface
{
    /**
     * @var mixed
     */
    public $a;

    /**
     * @var mixed
     */
    public $b;

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return array(
            'a' => $this->a,
            'b' => $this->b
        );
    }

    /**
     * @param array $array
     */
    public function exchangeArray(array $array)
    {
        $this->a = isset($array['a']) ? $array['a'] : null;
        $this->b = isset($array['b']) ? $array['b'] : null;
    }
}