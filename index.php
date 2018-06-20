<?php

use JDesrosiers\HypermediaTasks\MongoDbCollectionStore;
use JDesrosiers\HypermediaTasks\MongoDbStore;
use JDesrosiers\Resourceful\Controller\GetResourceController;
use JDesrosiers\Resourceful\CrudControllerProvider\CrudControllerProvider;
use JDesrosiers\Resourceful\FileCache\FileCache;
use JDesrosiers\Resourceful\IndexControllerProvider\IndexControllerProvider;
use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider;
use JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
$app["debug"] = true;
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
$app->mount("/", new IndexControllerProvider($static));

// Register Controllers
$taskData = new MongoDbStore($dbUri, $dbname, "/task/");
$app->mount("/task", new CrudControllerProvider("task", $taskData));

$taskListData = new MongoDbCollectionStore($dbUri, $dbname);
$app->get("/task/", new GetResourceController($taskListData))
    ->after(function() use ($app) {
        $app["json-schema.describedBy"] = "/schema/task-list";
    });

// Initialize CORS support
$app["cors-enabled"]($app);

$app->run();
