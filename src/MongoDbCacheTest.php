<?php

namespace JDesrosiers\HypermediaTasks;

use MongoDB\Client;
use PHPUnit\Framework\TestCase;

class MongoDbCacheTest extends TestCase
{
    private static $dbName;
    private static $client;
    private static $service;

    public static function setUpBeforeClass()
    {
        self::$dbName = getenv("MONGODB_DBNAME");
        $dbUri = getenv("MONGODB_URI");
        self::$client = new Client($dbUri);
        self::$service = new MongoDbCache($dbUri, self::$dbName, "testCollection");
        self::resetDb();
    }

    public function tearDown()
    {
        self::resetDb();
    }

    private static function resetDb()
    {
        self::$client->{self::$dbName}->dropCollection("testCollection");
    }

    public function testStoreNewObject()
    {
        $document = ["foo" => "bar"];
        $this->assertTrue(self::$service->save("some-id", $document));
        $this->assertEquals($document, self::$service->fetch("some-id"));
    }

    public function testReplaceObject()
    {
        $original = ["foo" => "bar"];
        $updated = ["foo" => "abc123"];
        $this->assertTrue(self::$service->save("some-id", $original));
        $this->assertTrue(self::$service->save("some-id", $updated));
        $this->assertEquals($updated, self::$service->fetch("some-id"));
    }

    public function testRetrieveNonexistentObject()
    {
        $this->assertFalse(self::$service->fetch("some-id"));
    }

    public function testDeleteObject()
    {
        $this->assertTrue(self::$service->save("some-id", ["foo" => "bar"]));
        $this->assertTrue(self::$service->delete("some-id"));
        $this->assertFalse(self::$service->fetch("some-id"));
    }

    public function testDeleteNonExistentObject()
    {
        $this->assertTrue(self::$service->delete("some-id"));
    }

    public function testGetStats()
    {
        $this->assertEquals(null, self::$service->getStats());
    }
}
