<?php

namespace JDesrosiers\HypermediaTasks;

use MongoDB\Client;
use PHPUnit\Framework\TestCase;

class MongoDbCollectionStoreTest extends TestCase
{
    private static $collection;
    private static $service;

    public static function setUpBeforeClass()
    {
        $dbName = getenv("MONGODB_DBNAME");
        $dbUri = getenv("MONGODB_URI");
        $client = new Client($dbUri);
        self::$collection = $client->{$dbName}->test;
        self::$service = new MongoDbCollectionStore($dbUri, $dbName, "test");
        self::reset();
    }

    public function setUp()
    {
        self::$collection->drop();
        self::$collection->insertMany([
            ["list" => "test", "aaa" => 111],
            ["list" => "test", "bbb" => 222],
            ["list" => "test", "ccc" => 333],
            ["list" => "test", "ddd" => 444],
            ["list" => "test", "eee" => 555],
            ["list" => "test", "fff" => 666]
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
            "id" => "test",
            "list" => [
                ["list" => "test", "aaa" => 111],
                ["list" => "test", "bbb" => 222],
                ["list" => "test", "ccc" => 333],
                ["list" => "test", "ddd" => 444],
                ["list" => "test", "eee" => 555]
            ],
            "page" => 0,
            "limit" => 5,
            "nextPage" => 1
        ];
        $this->assertEquals($expect, self::$service->fetch("/tasks/test?page=0&limit=5"));
    }

    public function testFetchTheSecondPageOfItems()
    {
        $expect = [
            "id" => "test",
            "list" => [
                ["list" => "test", "eee" => 555],
                ["list" => "test", "fff" => 666]
            ],
            "page" => 1,
            "limit" => 4,
            "nextPage" => 2,
            "prevPage" => 0
        ];
        $this->assertEquals($expect, self::$service->fetch("/tasks/test?page=1&limit=4"));
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
            "id" => "some-list",
            "list" => [],
            "page" => 0,
            "limit" => 5,
            "nextPage" => 1
        ];
        $this->assertEquals($expected, self::$service->fetch("/tasks/some-list?page=0&limit=5"));
    }

    public function testDeleteObject()
    {
        $this->assertTrue(self::$service->delete("/tasks/test?page=0&limit=5"));
        $this->assertEquals(1, self::$collection->count());
    }

    public function testGetStats()
    {
        $this->assertEquals(null, self::$service->getStats());
    }
}
