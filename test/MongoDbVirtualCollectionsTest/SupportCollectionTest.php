<?php

namespace MongoDbVirtualCollectionsTest;

use MongoDbVirtualCollectionsTest\Concrete\SupportCollection\SupportCollection;

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
}