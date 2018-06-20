<?php

use JDesrosiers\HypermediaTasks\MongoDbCache;
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

$static = new FileCache(__DIR__ . "/static");

// Register Supporting Controllers
$app->mount("/schema", new SchemaControllerProvider());
$app->flush();
$app->mount("/", new IndexControllerProvider($static));

// Register Controllers
$dbUri = getenv("MONGODB_URI");
$dbname = getenv("MONGODB_DBNAME");
$tasks = (new MongoDB\Client($dbUri))->$dbname->{"/schema/task"};
$data = new MongoDbCache($dbUri, $dbname, "/schema/task");
$app->mount("/task", new CrudControllerProvider("task", $data));
$app->get("/task/", function (Resourceful $app, Request $request) use ($tasks) {
    $app["json-schema.describedBy"] = "/schema/task-list";

    $resource = [
        "list" => array_map(function ($task) use ($data) {
            unset($task["_id"]);
            return $task;
        }, $tasks->find()->toArray())
    ];

    $response = JsonResponse::create($resource);
    return $app["allow"]($request, $response, $app);
});

// Initialize CORS support
$app["cors-enabled"]($app);

$app->run();
