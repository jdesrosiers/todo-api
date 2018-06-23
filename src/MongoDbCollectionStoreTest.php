<?php

namespace JDesrosiers\HypermediaTasks;

use MongoDB\Client;
use PHPUnit\Framework\TestCase;

class MongoDbCollectionStoreTest extends TestCase
{
    private static $collection;
    private static $service;
    private static $listName;

    public static function setUpBeforeClass()
    {
        $dbName = getenv("MONGODB_DBNAME");
        $dbUri = getenv("MONGODB_URI");
        $client = new Client($dbUri);
        self::$listName = "/test/";
        self::$collection = $client->{$dbName}->{self::$listName};
        self::$service = new MongoDbCollectionStore($dbUri, $dbName);
        self::reset();
    }

    public function setUp()
    {
        self::$collection->drop();
        self::$collection->insertMany([
            ["foo" => "bar"]
        ]);
    }

    public function tearDown()
    {
        self::reset();
    }

    private static function reset()
    {
        self::$collection->drop();
    }

    public function testFetchAList()
    {
        $expect = [
            "list" => [
                ["foo" => "bar"]
            ]
        ];
        $this->assertEquals($expect, self::$service->fetch(self::$listName));
    }

    //public function testReplaceObject()
    //{
        //$original = ["foo" => "bar"];
        //$updated = ["foo" => "abc123"];
        //$this->assertTrue(self::$service->save("some-id", $original));
        //$this->assertTrue(self::$service->save("some-id", $updated));
        //$this->assertEquals($updated, self::$service->fetch("some-id"));
    //}

    public function testRetrieveNonexistentObject()
    {
        $expected = [
            "list" => []
        ];
        $this->assertEquals($expected, self::$service->fetch("/some-list/"));
    }

    public function testDeleteObject()
    {
        $this->assertTrue(self::$service->delete(self::$listName));
        $this->assertEquals(0, self::$collection->count());
    }

    public function testGetStats()
    {
        $this->assertEquals(null, self::$service->getStats());
    }
}
