<?php

return function ($container) {
    // Database Connection
    $container[PDO::class] = function ($c) {
        $settings = $c->get('settings')['db'];
        $dsn = sprintf(
            '%s:host=%s;dbname=%s;charset=%s',
            $settings['driver'],
            $settings['host'],
            $settings['database'],
            $settings['charset']
        );
        return new PDO($dsn, $settings['username'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    };

    // Repositories
    $container['App\Infrastructure\Persistence\EstruturaRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\EstruturaRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\StatusIndicadoresRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\StatusIndicadoresRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\RealizadoRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\RealizadoRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\MetaRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\MetaRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\VariavelRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\VariavelRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\ProdutoRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\ProdutoRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\CalendarioRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\CalendarioRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\CampanhasRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\CampanhasRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\DetalhesRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\DetalhesRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\HistoricoRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\HistoricoRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\LeadsRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\LeadsRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\OmegaUsersRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\OmegaUsersRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\OmegaStatusRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\OmegaStatusRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\OmegaStructureRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\OmegaStructureRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\OmegaTicketsRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\OmegaTicketsRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\OmegaMesuRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\OmegaMesuRepository($c->get(PDO::class));
    };

    // Use Cases
    $container['App\Application\UseCase\FiltrosUseCase'] = function ($c) {
        return new \App\Application\UseCase\FiltrosUseCase(
            $c->get('App\Infrastructure\Persistence\EstruturaRepository'),
            $c->get('App\Infrastructure\Persistence\StatusIndicadoresRepository')
        );
    };

    $container['App\Application\UseCase\ResumoUseCase'] = function ($c) {
        return new \App\Application\UseCase\ResumoUseCase(
            $c->get('App\Infrastructure\Persistence\RealizadoRepository'),
            $c->get('App\Infrastructure\Persistence\MetaRepository')
        );
    };

    $container['App\Application\UseCase\StatusIndicadoresUseCase'] = function ($c) {
        return new \App\Application\UseCase\StatusIndicadoresUseCase(
            $c->get('App\Infrastructure\Persistence\StatusIndicadoresRepository')
        );
    };

    $container['App\Application\UseCase\AgentUseCase'] = function ($c) {
        return new \App\Application\UseCase\AgentUseCase();
    };

    $container['App\Application\UseCase\OmegaUsersUseCase'] = function ($c) {
        return new \App\Application\UseCase\OmegaUsersUseCase(
            $c->get('App\Infrastructure\Persistence\OmegaUsersRepository')
        );
    };

    $container['App\Application\UseCase\OmegaStatusUseCase'] = function ($c) {
        return new \App\Application\UseCase\OmegaStatusUseCase(
            $c->get('App\Infrastructure\Persistence\OmegaStatusRepository')
        );
    };

    $container['App\Application\UseCase\OmegaStructureUseCase'] = function ($c) {
        return new \App\Application\UseCase\OmegaStructureUseCase(
            $c->get('App\Infrastructure\Persistence\OmegaStructureRepository')
        );
    };

    $container['App\Application\UseCase\OmegaTicketsUseCase'] = function ($c) {
        return new \App\Application\UseCase\OmegaTicketsUseCase(
            $c->get('App\Infrastructure\Persistence\OmegaTicketsRepository')
        );
    };

    $container['App\Application\UseCase\OmegaMesuUseCase'] = function ($c) {
        return new \App\Application\UseCase\OmegaMesuUseCase(
            $c->get('App\Infrastructure\Persistence\OmegaMesuRepository')
        );
    };

    $container['App\Application\UseCase\DetalhesUseCase'] = function ($c) {
        return new \App\Application\UseCase\DetalhesUseCase(
            $c->get('App\Infrastructure\Persistence\DetalhesRepository')
        );
    };

    $container['App\Application\UseCase\EstruturaUseCase'] = function ($c) {
        return new \App\Application\UseCase\EstruturaUseCase(
            $c->get('App\Infrastructure\Persistence\EstruturaRepository')
        );
    };

    $container['App\Application\UseCase\LeadsUseCase'] = function ($c) {
        return new \App\Application\UseCase\LeadsUseCase(
            $c->get('App\Infrastructure\Persistence\LeadsRepository')
        );
    };

    $container['App\Application\UseCase\RealizadoUseCase'] = function ($c) {
        return new \App\Application\UseCase\RealizadoUseCase(
            $c->get('App\Infrastructure\Persistence\RealizadoRepository')
        );
    };

    $container['App\Application\UseCase\VariavelUseCase'] = function ($c) {
        return new \App\Application\UseCase\VariavelUseCase(
            $c->get('App\Infrastructure\Persistence\VariavelRepository')
        );
    };

    $container['App\Application\UseCase\CampanhasUseCase'] = function ($c) {
        return new \App\Application\UseCase\CampanhasUseCase(
            $c->get('App\Infrastructure\Persistence\CampanhasRepository')
        );
    };

    $container['App\Application\UseCase\ProdutoUseCase'] = function ($c) {
        return new \App\Application\UseCase\ProdutoUseCase(
            $c->get('App\Infrastructure\Persistence\ProdutoRepository')
        );
    };

    $container['App\Application\UseCase\MetaUseCase'] = function ($c) {
        return new \App\Application\UseCase\MetaUseCase(
            $c->get('App\Infrastructure\Persistence\MetaRepository')
        );
    };

    $container['App\Application\UseCase\HistoricoUseCase'] = function ($c) {
        return new \App\Application\UseCase\HistoricoUseCase(
            $c->get('App\Infrastructure\Persistence\HistoricoRepository')
        );
    };

    $container['App\Application\UseCase\CalendarioUseCase'] = function ($c) {
        return new \App\Application\UseCase\CalendarioUseCase(
            $c->get('App\Infrastructure\Persistence\CalendarioRepository')
        );
    };

    // Controllers
    $container['App\Presentation\Controllers\HealthController'] = function ($c) {
        return new \App\Presentation\Controllers\HealthController($c);
    };

    $container['App\Presentation\Controllers\AgentController'] = function ($c) {
        return new \App\Presentation\Controllers\AgentController($c);
    };

    $container['App\Presentation\Controllers\CalendarioController'] = function ($c) {
        return new \App\Presentation\Controllers\CalendarioController($c);
    };

    $container['App\Presentation\Controllers\CampanhasController'] = function ($c) {
        return new \App\Presentation\Controllers\CampanhasController($c);
    };

    $container['App\Presentation\Controllers\DetalhesController'] = function ($c) {
        return new \App\Presentation\Controllers\DetalhesController($c);
    };

    $container['App\Presentation\Controllers\EstruturaController'] = function ($c) {
        return new \App\Presentation\Controllers\EstruturaController($c);
    };

    $container['App\Presentation\Controllers\FiltrosController'] = function ($c) {
        return new \App\Presentation\Controllers\FiltrosController($c);
    };

    $container['App\Presentation\Controllers\HistoricoController'] = function ($c) {
        return new \App\Presentation\Controllers\HistoricoController($c);
    };

    $container['App\Presentation\Controllers\LeadsController'] = function ($c) {
        return new \App\Presentation\Controllers\LeadsController($c);
    };

    $container['App\Presentation\Controllers\MetasController'] = function ($c) {
        return new \App\Presentation\Controllers\MetasController($c);
    };

    $container['App\Presentation\Controllers\RealizadosController'] = function ($c) {
        return new \App\Presentation\Controllers\RealizadosController($c);
    };

    $container['App\Presentation\Controllers\ProdutosController'] = function ($c) {
        return new \App\Presentation\Controllers\ProdutosController($c);
    };

    $container['App\Presentation\Controllers\ResumoController'] = function ($c) {
        return new \App\Presentation\Controllers\ResumoController($c);
    };

    $container['App\Presentation\Controllers\StatusIndicadoresController'] = function ($c) {
        return new \App\Presentation\Controllers\StatusIndicadoresController($c);
    };

    $container['App\Presentation\Controllers\VariavelController'] = function ($c) {
        return new \App\Presentation\Controllers\VariavelController($c);
    };

    $container['App\Presentation\Controllers\MesuController'] = function ($c) {
        return new \App\Presentation\Controllers\MesuController($c);
    };

    $container['App\Presentation\Controllers\OmegaMesuController'] = function ($c) {
        return new \App\Presentation\Controllers\OmegaMesuController($c);
    };

    $container['App\Presentation\Controllers\OmegaStatusController'] = function ($c) {
        return new \App\Presentation\Controllers\OmegaStatusController($c);
    };

    $container['App\Presentation\Controllers\OmegaStructureController'] = function ($c) {
        return new \App\Presentation\Controllers\OmegaStructureController($c);
    };

    $container['App\Presentation\Controllers\OmegaTicketsController'] = function ($c) {
        return new \App\Presentation\Controllers\OmegaTicketsController($c);
    };

    $container['App\Presentation\Controllers\OmegaUsersController'] = function ($c) {
        return new \App\Presentation\Controllers\OmegaUsersController($c);
    };
};

