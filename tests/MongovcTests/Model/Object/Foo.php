<?php

namespace MongovcTests\Model\Object;

use Mongovc\Model\AbstractObject;

/**
 * Class Foo
 * @package MongovcTests\Model\Object
 */
class Foo extends AbstractObject
{
    public $a;

    public $b;

    /*
     * This method is solely dedicated to tests
     */
    public function testMultipleInitialization()
    {
        $this->initialize();
        $this->initialize();

        return true;
    }
}