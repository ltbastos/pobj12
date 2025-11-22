<?php

// Rota principal
$app->get('/', function ($request, $response) use ($app) {
    return $app->getContainer()->get('view')->render($response, 'index.twig', [
        'API_URL' => getenv('API_URL') ?: '/api',
        'API_HTTP_BASE' => getenv('API_HTTP_BASE') ?: '',
    ]);
});

// Rota Omega standalone
$app->get('/omega', function ($request, $response) use ($app) {
    return $app->getContainer()->get('view')->render($response, 'omega.twig');
});

// Rota 404
$app->get('/404', function ($request, $response) use ($app) {
    return $app->getContainer()->get('view')->render($response, '404.twig');
});