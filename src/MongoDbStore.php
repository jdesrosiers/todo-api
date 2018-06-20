<?php

namespace JDesrosiers\HypermediaTasks;

use Doctrine\Common\Cache\Cache;
use MongoDB\Client;

class MongoDbStore implements Cache
{
    private $collection;

    public function __construct($uri, $dbname, $collectionName)
    {
        $client = new Client($uri);
        $this->collection = $client->$dbname->$collectionName;
    }

    public function fetch($id)
    {
        $document = $this->collection->findOne(["_id" => $id]);
        unset($document["_id"]);

        return $document === null ? false : iterator_to_array($document);
    }

    public function contains($id)
    {
        return $this->fetch($id) !== false;
    }

    public function save($id, $data, $lifeTime = null)
    {
        $this->collection->replaceOne(["_id" => $id], $data, ["upsert" => true]);

        return true;
    }

    public function delete($id)
    {
        $this->collection->deleteOne(["_id" => $id]);

        return true;
    }

    public function getStats()
    {
        return null;
    }
}
