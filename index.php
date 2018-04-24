<?php

use JDesrosiers\Resourceful\Controller\DeleteResourceController;
use JDesrosiers\Resourceful\Controller\GetResourceController;
use JDesrosiers\Resourceful\Controller\PutResourceController;
use JDesrosiers\Resourceful\FileCache\FileCache;
use JDesrosiers\Resourceful\IndexControllerProvider\IndexControllerProvider;
use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider;
use JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
//$app["debug"] = true;
$app["cors.exposeHeaders"] = "allow, if-modified-since, link, location";

$app->register(new ResourcefulServiceProvider(), [
    "resourceful.schema-dir" => __DIR__
]);

$data = new FileCache(__DIR__ . "/data");
$static = new FileCache(__DIR__ . "/static");

// Register Supporting Controllers
$app->mount("/schema", new SchemaControllerProvider());
$app->flush();
$index = (new IndexControllerProvider($static))->connect($app);
$index->after(function (Request $request, Response $response) {
    $rel = $request->getSchemeAndHttpHost() . "/rel/todo";
    $response->headers->set("Link", "</todo/1>; rel=\"$rel\"; title=\"TODO List\"", false);
});
$app->mount("/", $index);

// Register Controllers
$schema = "/schema/todo";
$resource = $app["resources_factory"]($schema);
$resource->get("/{id}", new GetResourceController($data));
$resource->delete("/{id}", new DeleteResourceController($data));
$resource->put("/{id}", new PutResourceController($data, $schema));
$app->mount("/todo", $resource);

// Generate Allow headers for all GET and PUT requests
$app->after(function (Request $request, Response $response, Resourceful $app) {
    if ($response->isSuccessful() && in_array($request->getMethod(), ["GET", "PUT"])) {
        return $app["allow"]($request, $response, $app);
    }
});

// Initialize CORS support
$app["cors-enabled"]($app);

$app->run();
