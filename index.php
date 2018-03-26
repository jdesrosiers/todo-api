<?php

use Doctrine\Common\Cache\FilesystemCache;
use JDesrosiers\Resourceful\Controller\GetResourceController;
use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\FileCache\FileCache;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
$app["debug"] = true;

$static = new FileCache(__DIR__ . "/static");

// Register Controllers
$app->get("/", new GetResourceController($static))
    ->after($app["allow"]);

// Initialize CORS support
$app["cors-enabled"]($app);

$app->run();
