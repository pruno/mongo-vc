<?php

namespace MongoDbVirtualCollectionsTest;

use MongoDbVirtualCollections\Model\AbstractVirtualCollection;
use MongoDbVirtualCollections\Model\CollectionAbstractFactory;
use MongoDbVirtualCollections\Model\VirtualCollectionAbstractFactory;
use MongoDbVirtualCollections\Service\MongoDbAbstractServiceFactory;
use MongoDbVirtualCollectionsTest\Concrete\Object\Bar;
use MongoDbVirtualCollectionsTest\Concrete\Object\Foo;
use MongoDbVirtualCollectionsTest\Concrete\SupportCollection\SupportCollection;
use MongoDbVirtualCollectionsTest\Concrete\VirtualCollection\BarCollection;
use MongoDbVirtualCollectionsTest\Concrete\VirtualCollection\FooCollection;

/**
 * Class VirtualCollectionTest
 * @package MongoDbVirtualCollectionsTest
 * @method \MongoDbVirtualCollections\Model\AbstractVirtualCollection getCollection()
 */
class VirtualCollectionTest extends AbstractCollectionTest
{
    /**
     * @var SupportCollection
     */
    protected $supportCollection;

    /**
     * @var BarCollection
     */
    protected $secondaryVirtualCollection;

    /**
     * @return SupportCollection
     */
    public function getSupportCollection()
    {
        if ($this->supportCollection === null) {
            $this->supportCollection = new SupportCollection($this->getDriver());
        }

        return $this->supportCollection;
    }

    /**
     * @return FooCollection
     */
    public function createCollection()
    {
        return new FooCollection($this->getSupportCollection());
    }

    /**
     * @return BarCollection
     */
    public function getSecondaryVirtualCollection()
    {
        if ($this->secondaryVirtualCollection === null) {
            return new BarCollection($this->getSupportCollection());
        }

        return $this->secondaryVirtualCollection;
    }

    public function tearDown()
    {
        if ($this->collection) {
            $this->collection = null;
        }

        if ($this->secondaryVirtualCollection) {
            $this->secondaryVirtualCollection = null;
        }

        if ($this->supportCollection) {
            $this->supportCollection->getCollection()->drop();
            $this->supportCollection = null;
        }
    }

    public function dummySecondaryInsert()
    {
        $this->getSecondaryVirtualCollection()->insert(array(
            'bar' => 'foo'
        ));
    }

    public function testCollectionAbstractFactory()
    {
        $conf = $this->getVirtualCollectionConfig();
        $factory = new VirtualCollectionAbstractFactory();

        $this->getServiceLocator()->addAbstractFactory(new MongoDbAbstractServiceFactory());
        $this->getServiceLocator()->addAbstractFactory(new CollectionAbstractFactory());

        $this->assertTrue(
            $factory->canCreateServiceWithName($this->getServiceLocator(), null, $conf['className']),
            'ServiceLocator can\'t create collection throught factory'
        );

        $this->assertTrue(
            $factory->createServiceWithName($this->getServiceLocator(), null, $conf['className']) instanceof AbstractVirtualCollection,
            'ServiceLocator created service is not an instance of AbstractVirtualCollection'
        );
    }

    // Otherwise @depends tag will fail
    public function testInsert()
    {
        parent::testInsert();
    }

    /**
     * @depends testInsert
     */
    public function testHybridFind()
    {
        $this->dummyInsert();
        $this->dummySecondaryInsert();

        $this->assertTrue(
            $this->getCollection()->find()->current() instanceof Foo,
            "select()->current() should return an instance of Foo"
        );

        $this->assertTrue(
            $this->getSecondaryVirtualCollection()->find()->current() instanceof Bar,
            "select()->current() should return an instance of Bar"
        );
    }

    /**
     * @depends testInsert
     */
    public function testHybridCount()
    {
        $this->dummyInsert();
        $this->dummySecondaryInsert();

        $this->assertEquals(
            $this->getCollection()->count(),
            1,
            "count() doesn't return the expected integer"
        );

        $this->assertEquals(
            $this->getSecondaryVirtualCollection()->count(),
            1,
            "count() doesn't return the expected integer"
        );

        $this->assertEquals(
            $this->getSupportCollection()->count(),
            2,
            "count() doesn't return the expected integer"
        );
    }

    /**
     * @depends testInsert
     * @depends testHybridCount
     */
    public function testHybridUpdate()
    {
        $this->dummyInsert();
        $this->dummySecondaryInsert();

        $this->getCollection()->update(array(), array(
            'update' => time()
        ));

        $this->assertEquals(
            $this->getCollection()->count(array(
                'update' => array('$exists' => true)
            )),
            1,
            "update() did not affected the virtual collection"
        );

        $this->assertEquals(
            $this->getSecondaryVirtualCollection()->count(array(
                'update' => array('$exists' => true)
            )),
            0,
            "update() affected the wrong virtual collection"
        );
    }

    /**
     * @depends testInsert
     * @depends testHybridCount
     */
    public function testHybridDelete()
    {
        $this->dummyInsert();
        $this->dummySecondaryInsert();

        $this->getCollection()->remove();

        $this->assertEquals(
            $this->getCollection()->count(),
            0,
            "delete() did not affected the virtual collection"
        );

        $this->assertEquals(
            $this->getSecondaryVirtualCollection()->count(),
            1,
            "delete() affected the wrong virtual collection"
        );
    }
}