<?php

/**
 * Configuração e inicialização da aplicação Slim
 * 
 * @param \Slim\App $app
 * @return \Slim\App
 */
return function (\Slim\App $app) {
    // Registrar middlewares
    registerMiddlewares($app);
    
    // Carregar rotas
    require __DIR__ . '/../routes/web.php';
    require __DIR__ . '/../routes/api.php';
    
    return $app;
};

/**
 * Registra todos os middlewares da aplicação
 * 
 * @param \Slim\App $app
 */
function registerMiddlewares(\Slim\App $app)
{
    // ErrorMiddleware deve ser o primeiro para capturar todos os erros
    $app->add(new \App\Middleware\ErrorMiddleware());
    
    // FilterMiddleware processa os query params e adiciona FilterDTO como atributo
    $app->add(new \App\Middleware\FilterMiddleware());
    
    // Adicione outros middlewares aqui conforme necessário
    // Exemplo: $app->add(new \App\Middleware\CorsMiddleware());
    // Exemplo: $app->add(new \App\Middleware\AuthMiddleware());
}

