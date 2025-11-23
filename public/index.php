<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

$settings = require __DIR__ . '/../config/settings.php';
$app = new \Slim\App($settings);

// Configurar Twig View
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

// Configurar aplicação (middlewares e rotas)
$configureApp = require __DIR__ . '/../config/app.php';
$configureApp($app);

$app->run();
