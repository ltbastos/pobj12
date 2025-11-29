<?php

function makeControllerHandler($container, $class, $method = 'handle')
{
    return function ($request, $response) use ($container, $class, $method) {
        $controller = $container->get($class);
        return $controller->$method($request, $response);
    };
}

$container = $app->getContainer();

$app->get('/api/health', makeControllerHandler($container, 'App\Presentation\Controllers\HealthController', 'check'));
$app->get('/api/resumo', makeControllerHandler($container, 'App\Presentation\Controllers\ResumoController'));
$app->post('/api/agent', makeControllerHandler($container, 'App\Presentation\Controllers\AgentController'));
$app->get('/api/filtros', makeControllerHandler($container, 'App\Presentation\Controllers\FiltrosController'));
$app->get('/api/status_indicadores', makeControllerHandler($container, 'App\Presentation\Controllers\StatusIndicadoresController'));
$app->get('/api/estrutura', makeControllerHandler($container, 'App\Presentation\Controllers\EstruturaController'));
$app->get('/api/produtos', makeControllerHandler($container, 'App\Presentation\Controllers\ProdutosController'));
$app->get('/api/produtos/mensais', makeControllerHandler($container, 'App\Presentation\Controllers\ProdutosController', 'handleMonthly'));
$app->get('/api/calendario', makeControllerHandler($container, 'App\Presentation\Controllers\CalendarioController'));
$app->get('/api/realizados', makeControllerHandler($container, 'App\Presentation\Controllers\RealizadosController'));
$app->get('/api/metas', makeControllerHandler($container, 'App\Presentation\Controllers\MetasController'));
$app->get('/api/variavel', makeControllerHandler($container, 'App\Presentation\Controllers\VariavelController'));
$app->get('/api/mesu', makeControllerHandler($container, 'App\Presentation\Controllers\MesuController'));
$app->get('/api/campanhas', makeControllerHandler($container, 'App\Presentation\Controllers\CampanhasController'));
$app->get('/api/detalhes', makeControllerHandler($container, 'App\Presentation\Controllers\DetalhesController'));
$app->get('/api/historico', makeControllerHandler($container, 'App\Presentation\Controllers\HistoricoController'));
$app->get('/api/leads', makeControllerHandler($container, 'App\Presentation\Controllers\LeadsController'));
$app->get('/api/pontos', makeControllerHandler($container, 'App\Presentation\Controllers\PontosController'));
$app->get('/api/ranking', makeControllerHandler($container, 'App\Presentation\Controllers\RankingController'));

$app->group('/api/omega', function () use ($app, $container) {
    $app->get('/users', makeControllerHandler($container, 'App\Presentation\Controllers\Omega\OmegaUsersController'));
    $app->get('/statuses', makeControllerHandler($container, 'App\Presentation\Controllers\Omega\OmegaStatusController'));
    $app->get('/structure', makeControllerHandler($container, 'App\Presentation\Controllers\Omega\OmegaStructureController'));
    $app->get('/tickets', makeControllerHandler($container, 'App\Presentation\Controllers\Omega\OmegaTicketsController'));
    $app->get('/mesu', makeControllerHandler($container, 'App\Presentation\Controllers\Omega\OmegaMesuController'));
});
