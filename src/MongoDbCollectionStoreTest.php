<?php

namespace JDesrosiers\HypermediaTasks;

use MongoDB\Client;
use PHPUnit\Framework\TestCase;

class MongoDbCollectionStoreTest extends TestCase
{
    private static $dbName;
    private static $client;
    private static $service;

    public static function setUpBeforeClass()
    {
        self::$dbName = getenv("MONGODB_DBNAME");
        $dbUri = getenv("MONGODB_URI");
        self::$client = new Client($dbUri);
        self::$service = new MongoDbCollectionStore($dbUri, self::$dbName);
        self::resetDb();
    }

    public function tearDown()
    {
        self::resetDb();
    }

    private static function resetDb()
    {
        self::$client->{self::$dbName}->drop();
    }

    public function testCreateNewList()
    {
        $document = ["list" => [
            ["foo" => "bar"]
        ]];
        $this->assertTrue(self::$service->save("some-list", $document));
        $this->assertEquals($document, self::$service->fetch("some-list"));
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
        $document = ["list" => [ ]];
        $this->assertEquals($document, self::$service->fetch("some-list"));
    }

    public function testDeleteObject()
    {
        $document = ["list" => [
            ["foo" => "bar"]
        ]];

        $this->assertTrue(self::$service->save("some-list", $document));
        $this->assertTrue(self::$service->delete("some-list"));
        $document = ["list" => [ ]];
        $this->assertEquals($document, self::$service->fetch("some-list"));
    }

    public function testGetStats()
    {
        $this->assertEquals(null, self::$service->getStats());
    }
}
