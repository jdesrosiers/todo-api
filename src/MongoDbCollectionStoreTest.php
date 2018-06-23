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
            ["aaa" => 111],
            ["bbb" => 222],
            ["ccc" => 333],
            ["ddd" => 444],
            ["eee" => 555],
            ["fff" => 666]
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

    public function testFetchTheFirstPageOfItems()
    {
        $expect = [
            "list" => [
                ["aaa" => 111],
                ["bbb" => 222],
                ["ccc" => 333],
                ["ddd" => 444],
                ["eee" => 555]
            ],
            "page" => 0,
            "limit" => 5,
            "nextPage" => 1
        ];
        $this->assertEquals($expect, self::$service->fetch(self::$listName . "?page=0&limit=5"));
    }

    public function testFetchTheSecondPageOfItems()
    {
        $expect = [
            "list" => [
                ["eee" => 555],
                ["fff" => 666]
            ],
            "page" => 1,
            "limit" => 4,
            "nextPage" => 2,
            "prevPage" => 0
        ];
        $this->assertEquals($expect, self::$service->fetch(self::$listName . "?page=1&limit=4"));
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
            "list" => [],
            "page" => 0,
            "limit" => 5,
            "nextPage" => 1
        ];
        $this->assertEquals($expected, self::$service->fetch("/some-list/?page=0&limit=5"));
    }

    public function testDeleteObject()
    {
        $this->assertTrue(self::$service->delete(self::$listName . "?page=0&limit=5"));
        $this->assertEquals(1, self::$collection->count());
    }

    public function testGetStats()
    {
        $this->assertEquals(null, self::$service->getStats());
    }
}
