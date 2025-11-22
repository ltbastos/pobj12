<?php

// Rotas da API

// Health Check
$app->get('/api/health', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\HealthController');
    return $controller->check($request, $response);
});

// Agent (POST)
$app->post('/api/agent', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\AgentController');
    return $controller->handle($request, $response);
});

// Filtros
$app->get('/api/filtros', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\FiltrosController');
    return $controller->handle($request, $response);
});

// Resumo
$app->get('/api/resumo', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\ResumoController');
    return $controller->handle($request, $response);
});

// Status Indicadores
$app->get('/api/status_indicadores', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\StatusIndicadoresController');
    return $controller->handle($request, $response);
});

// Estrutura
$app->get('/api/estrutura', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\EstruturaController');
    return $controller->handle($request, $response);
});

// Produtos
$app->get('/api/produtos', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\ProdutosController');
    return $controller->handle($request, $response);
});

// Calendário
$app->get('/api/calendario', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\CalendarioController');
    return $controller->handle($request, $response);
});

// Realizados
$app->get('/api/realizados', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\RealizadosController');
    return $controller->handle($request, $response);
});

// Metas
$app->get('/api/metas', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\MetasController');
    return $controller->handle($request, $response);
});

// Variável
$app->get('/api/variavel', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\VariavelController');
    return $controller->handle($request, $response);
});

// MESU
$app->get('/api/mesu', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\MesuController');
    return $controller->handle($request, $response);
});

// Campanhas
$app->get('/api/campanhas', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\CampanhasController');
    return $controller->handle($request, $response);
});

// Detalhes
$app->get('/api/detalhes', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\DetalhesController');
    return $controller->handle($request, $response);
});

// Histórico
$app->get('/api/historico', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\HistoricoController');
    return $controller->handle($request, $response);
});

// Leads
$app->get('/api/leads', function ($request, $response) use ($app) {
    $controller = $app->getContainer()->get('App\Presentation\Controllers\LeadsController');
    return $controller->handle($request, $response);
});

// Rotas Omega
$app->group('/api/omega', function () use ($app) {
    // Omega Users
    $app->get('/users', function ($request, $response) use ($app) {
        $controller = $app->getContainer()->get('App\Presentation\Controllers\OmegaUsersController');
        return $controller->handle($request, $response);
    });
    
    // Omega Statuses
    $app->get('/statuses', function ($request, $response) use ($app) {
        $controller = $app->getContainer()->get('App\Presentation\Controllers\OmegaStatusController');
        return $controller->handle($request, $response);
    });
    
    // Omega Structure
    $app->get('/structure', function ($request, $response) use ($app) {
        $controller = $app->getContainer()->get('App\Presentation\Controllers\OmegaStructureController');
        return $controller->handle($request, $response);
    });
    
    // Omega Tickets
    $app->get('/tickets', function ($request, $response) use ($app) {
        $controller = $app->getContainer()->get('App\Presentation\Controllers\OmegaTicketsController');
        return $controller->handle($request, $response);
    });
    
    // Omega MESU
    $app->get('/mesu', function ($request, $response) use ($app) {
        $controller = $app->getContainer()->get('App\Presentation\Controllers\OmegaMesuController');
        return $controller->handle($request, $response);
    });
});
