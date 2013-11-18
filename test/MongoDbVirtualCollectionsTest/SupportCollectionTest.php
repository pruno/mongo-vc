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
        return new SupportCollection($this->getDriver());
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

    public function testFind()
    {
        try {
            parent::testFind();
        } catch (\Exception $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail("Support collection should throw an exception while creating objects");
    }

    public function testFindOne()
    {
        try {
            parent::testFindOne();
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
        parent::testInsert();
    }

    // Otherwise @depends tag will fail
    public function testFindRaw()
    {
        parent::testFindRaw();
    }

    public function testGetById()
    {
        // This test is handled by VirtualCollectionTest test class
    }
}