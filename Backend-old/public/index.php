<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$_SERVER['REQUEST_URI'] = $requestUri;

function sendErrorResponse(\Throwable $e, bool $includeTrace = false): void
{
    http_response_code(500);
    header('Content-Type: application/json');
    $response = [
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
    if ($includeTrace) {
        $response['trace'] = $e->getTraceAsString();
    }
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

try {
    require __DIR__ . '/../vendor/autoload.php';
    
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
    
    $app = new \Slim\App(require __DIR__ . '/../config/settings.php');
} catch (\Throwable $e) {
    sendErrorResponse($e);
}

try {
    $container = $app->getContainer();
    (require __DIR__ . '/../config/dependencies.php')($container);
    $container->get('db');
    (require __DIR__ . '/../config/app.php')($app);
    $app->run();
} catch (\Throwable $e) {
    sendErrorResponse($e, true);
}
