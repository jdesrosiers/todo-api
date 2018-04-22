<?php

use JDesrosiers\Resourceful\Controller\DeleteResourceController;
use JDesrosiers\Resourceful\Controller\GetResourceController;
use JDesrosiers\Resourceful\Controller\PutResourceController;
use JDesrosiers\Resourceful\FileCache\FileCache;
use JDesrosiers\Resourceful\Resourceful;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
//$app["debug"] = true;
$app["cors.exposeHeaders"] = "allow, if-modified-since, link, location";

$data = new FileCache(__DIR__ . "/data");
$static = new FileCache(__DIR__ . "/static");
$schemaService = new FileCache(__DIR__);

// Register Controllers
$app->get("/", new GetResourceController($static))
    ->after(function (Request $request, Response $response) {
        $rel = $request->getSchemeAndHttpHost() . "/rel/todo";
        $response->headers->set("Link", "</todo/1>; rel=\"$rel\"; title=\"TODO List\"", false);
    });

$schema = "/schema/todo";
$app["json-schema.schema-store"]->add($schema, $schemaService->fetch($schema));
$app->get("/todo/{id}", new GetResourceController($data));
$app->delete("/todo/{id}", new DeleteResourceController($data));
$app->put("/todo/{id}", new PutResourceController($data, $schema));

// Generate Allow headers for all GET and PUT requests
$app->after(function ($request, $response, $app) {
    if ($response->isSuccessful() && in_array($request->getMethod(), ["GET", "PUT"])) {
        return $app["allow"]($request, $response, $app);
    }
});

// Initialize CORS support
$app["cors-enabled"]($app);

$app->run();
