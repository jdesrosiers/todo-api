<?php

namespace JDesrosiers\HypermediaTasks;

use Doctrine\Common\Cache\Cache;
use MongoDB\Client;

class MongoDbCollectionStore implements Cache
{
    private $db;

    public function __construct($uri, $dbname)
    {
        $client = new Client($uri);
        $this->db = $client->$dbname;
    }

    public function fetch($id)
    {
        return [
            "list" => array_map(function ($task) {
                unset($task["_id"]);
                return iterator_to_array($task);
            }, $this->db->$id->find()->toArray())
        ];
    }

    public function contains($id)
    {
        return true;
    }

    public function save($id, $data, $lifeTime = null)
    {
        $this->db->createCollection($id);
        foreach ($data["list"] as $item) {
            $this->db->$id->replaceOne(["_id" => $item["id"]], $item, ["upsert" => true]);
        }

        return true;
    }

    public function delete($id)
    {
        $this->db->dropCollection($id);
        return true;
    }

    public function getStats()
    {
        return null;
    }
}
