<?php
// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 0); // Não exibir na tela, mas logar
ini_set('log_errors', 1);

$basePath = '/pobj-slim/public';

if (strpos($_SERVER['REQUEST_URI'], $basePath) === 0) {
    $requestUri = substr($_SERVER['REQUEST_URI'], strlen($basePath));
} else {
    $requestUri = $_SERVER['REQUEST_URI'];
}

// Remover o prefixo /pobj-slim se existir (normalizar a URI)
$requestUri = preg_replace('#^/pobj-slim#', '', $requestUri);
if (empty($requestUri)) {
    $requestUri = '/';
}

// Verificar se é uma rota de API
$isApiRoute = strpos($requestUri, '/api/') === 0;

// Se for rota de API, pular para o Slim abaixo
if (!$isApiRoute) {
    // Mapear imagens do Vue: /img/* (se não existir em /img/, tenta em /assets/img/)
    if (preg_match('#^/img/(.+)$#', $requestUri, $matches)) {
        $imgPathPublic = __DIR__ . '/img/' . $matches[1];
        $imgPathAssets = __DIR__ . '/assets/img/' . $matches[1];
        
        // Primeiro tenta em /img/, depois em /assets/img/
        if (file_exists($imgPathPublic) && is_file($imgPathPublic)) {
            $mimeType = @mime_content_type($imgPathPublic) ?: 'application/octet-stream';
            header('Content-Type: ' . $mimeType);
            @readfile($imgPathPublic);
            exit;
        } elseif (file_exists($imgPathAssets) && is_file($imgPathAssets)) {
            $mimeType = @mime_content_type($imgPathAssets) ?: 'application/octet-stream';
            header('Content-Type: ' . $mimeType);
            @readfile($imgPathAssets);
            exit;
        }
    }
    
    // Se chegou aqui, é uma rota do Vue SPA - servir o index.html
    $vueIndexPath = __DIR__ . '/index.html';
    if (file_exists($vueIndexPath)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($vueIndexPath);
        exit;
    }
    
    // Se o index.html não existir, retornar 404
    http_response_code(404);
    echo 'Vue build não encontrado. Execute: npm run build na pasta resources/frontend';
    exit;
}

// Para rotas de API, inicializar o Slim
// Ajustar REQUEST_URI antes de inicializar o Slim para que ele use a URI normalizada
$_SERVER['REQUEST_URI'] = $requestUri;

try {
    require __DIR__ . '/../vendor/autoload.php';

    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
    $dotenv->load();

    $settings = require __DIR__ . '/../config/settings.php';

    $app = new \Slim\App($settings);
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
    exit;
}

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
try {
    $dependencies = require __DIR__ . '/../config/dependencies.php';
    $dependencies($app->getContainer());

    // Inicializar o Capsule para uso global (com tratamento de erro)
    // Não inicializar aqui - deixar lazy loading
    // $app->getContainer()->get('db');

    // Configurar aplicação (middlewares e rotas)
    $configureApp = require __DIR__ . '/../config/app.php';
    $configureApp($app);

    $app->run();
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
    exit;
}
