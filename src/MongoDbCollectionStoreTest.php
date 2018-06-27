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
            ["id" => "1", "list" => "test", "aaa" => 111],
            ["id" => "2", "list" => "test", "bbb" => 222],
            ["id" => "3", "list" => "test", "ccc" => 333],
            ["id" => "4", "list" => "test", "ddd" => 444],
            ["id" => "5", "list" => "test", "eee" => 555],
            ["id" => "6", "list" => "test", "fff" => 666]
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
                ["id" => "1", "list" => "test", "aaa" => 111],
                ["id" => "2", "list" => "test", "bbb" => 222],
                ["id" => "3", "list" => "test", "ccc" => 333],
                ["id" => "4", "list" => "test", "ddd" => 444],
                ["id" => "5", "list" => "test", "eee" => 555]
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
                ["id" => "5", "list" => "test", "eee" => 555],
                ["id" => "6", "list" => "test", "fff" => 666]
            ],
            "page" => 1,
            "limit" => 4,
            "nextPage" => 2,
            "prevPage" => 0
        ];
        $this->assertEquals($expect, self::$service->fetch("/tasks/test?page=1&limit=4"));
    }

    public function testReplaceObject()
    {
        $document = [
            "id" => "test",
            "list" => [
                ["id" => "1", "list" => "test", "aaa" => 111],
                ["id" => "2", "list" => "test", "bbb" => 222],
            ],
            "page" => 0,
            "limit" => 2,
            "nextPage" => 1
        ];
        $object = json_decode(json_encode($document));
        $this->assertTrue(self::$service->save("/tasks/test?page=0&limit=2", $object));
        $this->assertEquals($document, self::$service->fetch("/tasks/test?page=0&limit=2"));
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function testSaveWithMissingItem()
    {
        $document = [
            "id" => "test",
            "list" => [
                ["id" => "1", "list" => "test", "aaa" => 111],
            ],
            "page" => 0,
            "limit" => 2,
            "nextPage" => 1
        ];
        $object = json_decode(json_encode($document));
        self::$service->save("/tasks/test?page=0&limit=2", $object);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function testSaveWithExtraItem()
    {
        $document = [
            "id" => "test",
            "list" => [
                ["id" => "1", "list" => "test", "aaa" => 111],
                ["id" => "2", "list" => "test", "bbb" => 222],
                ["id" => "3", "list" => "test", "ccc" => 333]
            ],
            "page" => 0,
            "limit" => 2,
            "nextPage" => 1
        ];
        $object = json_decode(json_encode($document));
        self::$service->save("/tasks/test?page=0&limit=2", $object);
    }

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
