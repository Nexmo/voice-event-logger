<?php

require __DIR__ . '/../vendor/autoload.php';

// pre-app setup, inc. loading env vars on development platforms
require __DIR__ . '/../setup.php';

$app = new Slim\App();

// configure logging etc
require __DIR__ . '/../config.php';


// temporary answer endpoint for testing purposes
$app->get('/webhooks/answer', function ($request, $response, $args) {
    $ncco = [
        ["action" => "talk", "text" => "Hello and welcome"]
    ];
    return $response->withJson($ncco);
});

// event logger
$app->map(['GET', 'POST'], '/event', function ($request, $response, $args) {
    $get_params = $request->getQueryParams();
    $post_params = $request->getParsedBody();

    $input_params = $get_params;

    if (is_array($post_params)) {
        $input_params = array_merge($input_params, $post_params);
    }

    $this->logger->info("Event: " . $input_params['status'], $input_params);
    return $response->getBody()->write("OK");
});

// event viewer
/* TODO build event viewer */

$app->run();
