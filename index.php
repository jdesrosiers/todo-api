<?php

use Doctrine\Common\Cache\MongoDBCache;
use JDesrosiers\Resourceful\CrudControllerProvider\CrudControllerProvider;
use JDesrosiers\Resourceful\FileCache\FileCache;
use JDesrosiers\Resourceful\IndexControllerProvider\IndexControllerProvider;
use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider;
use JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
$app["debug"] = true;
$app["cors.exposeHeaders"] = "allow, if-modified-since, link, location";

$app->register(new ResourcefulServiceProvider(), [
    "resourceful.schema-dir" => __DIR__
]);

$db = getenv("MONGODB_DBNAME");
$mongo = (new MongoDB\Client(getenv("MONGODB_URI")))->$db;
$static = new FileCache(__DIR__ . "/static");

// Register Supporting Controllers
$app->mount("/schema", new SchemaControllerProvider());
$app->flush();
$app->mount("/", new IndexControllerProvider($static));

// Register Controllers
$data = new MongoDBCache($mongo->task);
$app->mount("/task", new CrudControllerProvider("task", $data));

// Initialize CORS support
$app["cors-enabled"]($app);

$app->run();
