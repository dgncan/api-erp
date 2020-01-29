<?php

use League\OAuth2\Server\Middleware\ResourceServerMiddleware;

$app->get('[/]', function ($request, $response, array $args) use ($app) {
    header('Content-Type: application/json');

    $settings = require __DIR__ . '/../src/openapi.json';
    exit($settings);
});


# Erp API
$app->group('/erp', function () use ($app) {
    new Service\Erp\Erp($app);
})->add(new ResourceServerMiddleware($app->getContainer()->get('resourceServer')));


$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});


$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});
