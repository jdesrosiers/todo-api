<?php

use JDesrosiers\HypermediaTasks\MongoDbCollectionListStore;
use JDesrosiers\HypermediaTasks\MongoDbCollectionStore;
use JDesrosiers\HypermediaTasks\MongoDbStore;
use JDesrosiers\Resourceful\Controller\GetResourceController;
use JDesrosiers\Resourceful\CrudControllerProvider\CrudControllerProvider;
use JDesrosiers\Resourceful\FileCache\FileCache;
use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider;
use JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
//$app["debug"] = true;
$app["cors.exposeHeaders"] = "allow, if-modified-since, link, location";

$app->register(new ResourcefulServiceProvider(), [
    "resourceful.schema-dir" => __DIR__
]);

$dbUri = getenv("MONGODB_URI");
$dbname = getenv("MONGODB_DBNAME");
$static = new FileCache(__DIR__ . "/static");

// Register Supporting Controllers
$app->mount("/schema", new SchemaControllerProvider());
$app->flush();

// Register Controllers
$taskListListData = new MongoDbCollectionListStore($dbUri, $dbname, "task");
$app->get("/", new GetResourceController($taskListListData))
    ->after(function () use ($app) {
        $app["json-schema.describedBy"] = "/schema/index";
    });

$taskListData = new MongoDbCollectionStore($dbUri, $dbname, "task");
$app->mount("/tasks", new CrudControllerProvider("task-list", $taskListData));

$taskData = new MongoDbStore($dbUri, $dbname, "task");
$app->mount("/task", new CrudControllerProvider("task", $taskData));

// Initialize CORS support
$app["cors-enabled"]($app);

$app->run();
