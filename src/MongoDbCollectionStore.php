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
        $query = $this->parseId($id);
        $options = ["skip" => $query["skip"], "limit" => $query["limit"]];
        $collection = $this->db->{$query["id"]};

        $list = [
            "list" => array_map(function ($task) {
                unset($task["_id"]);
                return iterator_to_array($task);
            }, $collection->find([], $options)->toArray()),
            "page" => $query["page"],
            "limit" => $query["limit"],
            "nextPage" => $query["page"] + 1
        ];

        if ($query["page"] > 0) {
            $list["prevPage"] = $query["page"] - 1;
        }

        return $list;
    }

    public function contains($id)
    {
        return true;
    }

    public function save($id, $data, $lifeTime = null)
    {
        //$this->db->dropCollection($id);
        //foreach ($data["list"] as $item) {
            //$this->db->$id->replaceOne(["_id" => $item["id"]], $item, ["upsert" => true]);
        //}

        //return true;
        return false;
    }

    public function delete($id)
    {
        $query = $this->parseId($id);
        $options = ["skip" => $query["skip"], "limit" => $query["limit"]];
        $collection = $this->db->{$query["id"]};

        $list = $collection->find([], $options)->toArray();
        foreach ($list as $item) {
            $collection->deleteOne(["_id" => $item["_id"]]);
        }

        return true;
    }

    public function getStats()
    {
        return null;
    }

    private function parseId($id)
    {
        $urlQuery = parse_url($id, PHP_URL_QUERY);
        parse_str($urlQuery, $query);
        $limit = intval($query["limit"]);
        $page = intval($query["page"]);

        return [
            "id" => parse_url($id, PHP_URL_PATH),
            "limit" => $limit,
            "page" => $page,
            "skip" => $limit * $page
        ];
    }
}
