<?php

// Obter URI da requisição
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Verificar se é uma rota de API
$isApiRoute = strpos($requestUri, '/api/') === 0;

// Se for rota de API, pular para o Slim abaixo
if (!$isApiRoute) {
    // Mapear assets do Vue: /assets/* -> /dist/assets/*
    if (preg_match('#^/assets/(.+)$#', $requestUri, $matches)) {
        $assetPath = __DIR__ . '/dist/assets/' . $matches[1];
        if (file_exists($assetPath) && is_file($assetPath)) {
            $mimeType = mime_content_type($assetPath);
            header('Content-Type: ' . $mimeType);
            readfile($assetPath);
            exit;
        }
    }
    
    // Mapear favicon: /favicon.ico -> /dist/favicon.ico
    if ($requestUri === '/favicon.ico') {
        $faviconPath = __DIR__ . '/dist/favicon.ico';
        if (file_exists($faviconPath)) {
            header('Content-Type: image/x-icon');
            readfile($faviconPath);
            exit;
        }
    }
    
    // Mapear fonts: /fonts/* -> /dist/fonts/*
    if (preg_match('#^/fonts/(.+)$#', $requestUri, $matches)) {
        $fontPath = __DIR__ . '/dist/fonts/' . $matches[1];
        if (file_exists($fontPath) && is_file($fontPath)) {
            $mimeType = mime_content_type($fontPath);
            header('Content-Type: ' . $mimeType);
            readfile($fontPath);
            exit;
        }
    }
    
    // Mapear imagens do Vue: /img/* -> /dist/img/* (se não existir em /img/)
    if (preg_match('#^/img/(.+)$#', $requestUri, $matches)) {
        $imgPathPublic = __DIR__ . '/img/' . $matches[1];
        $imgPathDist = __DIR__ . '/dist/img/' . $matches[1];
        
        // Primeiro tenta em /img/, depois em /dist/img/
        if (file_exists($imgPathPublic) && is_file($imgPathPublic)) {
            $mimeType = mime_content_type($imgPathPublic);
            header('Content-Type: ' . $mimeType);
            readfile($imgPathPublic);
            exit;
        } elseif (file_exists($imgPathDist) && is_file($imgPathDist)) {
            $mimeType = mime_content_type($imgPathDist);
            header('Content-Type: ' . $mimeType);
            readfile($imgPathDist);
            exit;
        }
    }
    
    // Verificar se é um arquivo estático existente (CSS, JS, etc. fora do dist)
    $staticPath = __DIR__ . $requestUri;
    if (file_exists($staticPath) && is_file($staticPath) && strpos($requestUri, '/dist/') !== 0) {
        $mimeType = mime_content_type($staticPath);
        header('Content-Type: ' . $mimeType);
        readfile($staticPath);
        exit;
    }
    
    // Se chegou aqui, é uma rota do Vue SPA - servir o index.html
    $vueIndexPath = __DIR__ . '/dist/index.html';
    if (file_exists($vueIndexPath)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($vueIndexPath);
        exit;
    }
    
    // Se o dist/index.html não existir, retornar 404
    http_response_code(404);
    echo 'Vue build não encontrado. Execute: npm run build na pasta resources/frontend';
    exit;
}

// Para rotas de API, inicializar o Slim
require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

$settings = require __DIR__ . '/../config/settings.php';
$app = new \Slim\App($settings);

// Configurar Twig View (opcional, caso ainda use para rotas específicas)
$container = $app->getContainer();
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig($container['settings']['view']['template_path'], [
        'cache' => $container['settings']['view']['twig']['cache'],
        'debug' => $container['settings']['view']['twig']['debug'],
        'auto_reload' => $container['settings']['view']['twig']['auto_reload'],
    ]);
    
    // Obter URI base para assets
    $request = $container->get('request');
    $uri = $request->getUri();
    $basePath = rtrim(str_replace('index.php', '', strtolower($uri->getBasePath())), '/');
    
    // Adicionar extensão para facilitar acesso a assets
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->get('router'),
        $uri
    ));
    
    // Adicionar função global para caminhos de assets
    $view->getEnvironment()->addGlobal('base_path', $basePath);
    
    return $view;
};

// Carregar dependências
$dependencies = require __DIR__ . '/../config/dependencies.php';
$dependencies($app->getContainer());

// Inicializar o Capsule para uso global
$container->get('db');

// Configurar aplicação (middlewares e rotas)
$configureApp = require __DIR__ . '/../config/app.php';
$configureApp($app);

$app->run();
