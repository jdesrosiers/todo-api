<?php

namespace JDesrosiers\HypermediaTasks;

use Doctrine\Common\Cache\Cache;
use MongoDB\Client;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MongoDbCollectionStore implements Cache
{
    private $collection;

    public function __construct($uri, $dbname, $collectionName)
    {
        $client = new Client($uri);
        $this->collection = $client->$dbname->$collectionName;
    }

    public function fetch($id)
    {
        $args = $this->parseId($id);
        $filter = ["list" => $args["list"]];
        $options = ["limit" => $args["limit"], "skip" => $args["skip"]];

        $list = [
            "id" => $args["list"],
            "list" => array_map(function ($task) {
                unset($task["_id"]);
                return iterator_to_array($task);
            }, $this->collection->find($filter, $options)->toArray()),
            "page" => $args["page"],
            "limit" => $args["limit"],
            "nextPage" => $args["page"] + 1
        ];

        if ($args["page"] > 0) {
            $list["prevPage"] = $args["page"] - 1;
        }

        return $list;
    }

    public function contains($id)
    {
        return true;
    }

    public function save($id, $data, $lifeTime = null)
    {
        $args = $this->parseId($id);
        $filter = ["list" => $args["list"]];
        $options = ["limit" => $args["limit"], "skip" => $args["skip"]];
        $list = $this->collection->find($filter, $options);

        $message = "Could not process edit request. Bulk edits do not " .
                   "support adding, removing, or reordering items";
        $operations = [];
        foreach ($list as $ndx => $item) {
            if ($item->id !== $data->list[$ndx]->id) {
                throw new UnprocessableEntityHttpException($message);
            }

            $operations[] = [
                "replaceOne" => [
                    ["id" => $item->id],
                    $data->list[$ndx]
                ]
            ];
        }

        if (count($operations) !== count($data->list)) {
            throw new UnprocessableEntityHttpException($message);
        }

        return $this->collection->bulkWrite($operations)->isAcknowledged();
    }

    public function delete($id)
    {
        $args = $this->parseId($id);
        $filter = ["list" => $args["list"]];
        $options = ["skip" => $args["skip"], "limit" => $args["limit"]];
        $list = $this->collection->find($filter, $options);

        $operations = array_map(function ($item) {
            return [
                "deleteOne" => [
                    ["_id" => $item["_id"]],
                ]
            ];
        }, $list->toArray());

        return $this->collection->bulkWrite($operations)->isAcknowledged();
    }

    public function getStats()
    {
        return null;
    }

    private function parseId($id)
    {
        preg_match('/\/([^\/]*)$/', parse_url($id, PHP_URL_PATH), $match);
        $list = $match[1];

        $urlQuery = parse_url($id, PHP_URL_QUERY);
        parse_str($urlQuery, $query);
        $limit = intval($query["limit"]);
        $page = intval($query["page"]);

        return [
            "list" => $list,
            "limit" => $limit,
            "page" => $page,
            "skip" => $limit * $page
        ];
    }
}
