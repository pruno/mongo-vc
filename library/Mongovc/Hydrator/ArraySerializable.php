<?php

namespace Mongovc\Hydrator;

use Zend\Stdlib\Exception\BadMethodCallException;
use Zend\Stdlib\Hydrator\ArraySerializable as ZendArraySerializable;

/**
 * Class ArraySerializable
 * @package Mongovc\Hydrator
 */
class ArraySerializable extends ZendArraySerializable
{
    /**
     * Hydrate an object
     *
     * Hydrates an object by passing $data to either its enhance(), exchangeArray() or populate() method.
     *
     * @param  array $data
     * @param  object $object
     * @return object
     * @throws BadMethodCallException
     */
    public function hydrate(array $data, $object)
    {
        if (!is_callable(array($object, 'enhance'))) {
            return parent::hydrate($data, $object);
        }

        $self = $this;
        array_walk($data, function (&$value, $name) use ($self) {
            $value = $self->hydrateValue($name, $value);
        });

        $object->enhance($data);

        return $object;
    }
}