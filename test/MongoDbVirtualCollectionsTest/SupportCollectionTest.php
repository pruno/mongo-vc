<?php

namespace MongoDbVirtualCollectionsTest;

use MongoDbVirtualCollectionsTest\Concrete\SupportCollection\SupportCollection;

/**
 * @methdo \MongoDbVirtualCollectionsTest\Concrete\SupportCollection\SupportCollection getCollection()
 */
class SupportCollectionTest extends AbstractCollectionTest
{
    public function createCollection()
    {
        return new SupportCollection($this->getServiceLocator(), $this->getDriver());
    }

    public function testCreateObject()
    {
        try {
            parent::testCreateObject();
        } catch (\Exception $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail("Support collection should throw an exception while creating objects");
    }

    public function testSelect()
    {
        try {
            parent::testSelect();
        } catch (\Exception $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail("Support collection should throw an exception while creating objects");
    }

    public function testSelectOne()
    {
        try {
            parent::testSelectOne();
        } catch (\Exception $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail("Support collection should throw an exception while creating objects");
    }

    public function testHydratingMongoCursor()
    {
        try {
            parent::testHydratingMongoCursor();
        } catch (\Exception $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail("Support collection should throw an exception while creating objects");
    }

    // Otherwise @depends tag will fail
    public function testInsert()
    {
        return parent::testInsert();
    }

    // Otherwise @depends tag will fail
    public function testSelectRawData()
    {
        return parent::testSelectRawData();
    }

    public function testGetById()
    {
        // This test is handled by VirtualCollectionTest test class
    }
}