<?php

namespace JDesrosiers\HypermediaTasks;

use Doctrine\Common\Cache\Cache;
use MongoDB\Client;

class MongoDbCollectionListStore implements Cache
{
    private $collection;

    public function __construct($uri, $dbname, $collectionName)
    {
        $client = new Client($uri);
        $this->collection = $client->$dbname->$collectionName;
    }

    public function fetch($id)
    {
        return [
            "title" => "TODO API",
            "description" => "A Hypermedia API for a TODO List application",
            "list" => $this->collection->distinct("list")
        ];
    }

    public function contains($id)
    {
        return true;
    }

    public function save($id, $data, $lifeTime = null)
    {
        return false;
    }

    public function delete($id)
    {
        return false;
    }

    public function getStats()
    {
        return null;
    }
}
