<?php

use Doctrine\Common\Cache\FilesystemCache;
use JDesrosiers\Resourceful\Controller\GetResourceController;
use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\FileCache\FileCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
$app["debug"] = true;
$app["cors.exposeHeaders"] = "if-modified-since, location, link";

$static = new FileCache(__DIR__ . "/static");

// Register Controllers
$app->get("/", new GetResourceController($static))
    ->after($app["allow"])
    ->after(function (Request $request, Response $response) {
        $response->headers->set("Link", "</foo>; rel=\"foo\"; title=\"Foo\"", false);
        $response->headers->set("Link", "</bar>; rel=\"bar\"; title=\"Bar\"", false);
    });

// Initialize CORS support
$app["cors-enabled"]($app);

$app->run();
