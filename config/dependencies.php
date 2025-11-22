<?php

use PDO;
use Doctrine\ORM\EntityManager;

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

    // EntityManager (se estiver usando Doctrine)
    // $container[EntityManager::class] = function ($c) {
    //     return DoctrineManager::getEntityManager();
    // };

    // Repositories
    $container['App\Infrastructure\Persistence\EstruturaRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\EstruturaRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\StatusIndicadoresRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\StatusIndicadoresRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\RealizadoRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\RealizadoRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\MetaRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\MetaRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\VariavelRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\VariavelRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\ProdutoRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\ProdutoRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\CalendarioRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\CalendarioRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\CampanhasRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\CampanhasRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\DetalhesRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\DetalhesRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\HistoricoRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\HistoricoRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\LeadsRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\LeadsRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\OmegaUsersRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\OmegaUsersRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\OmegaStatusRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\OmegaStatusRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\OmegaStructureRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\OmegaStructureRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\OmegaTicketsRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\OmegaTicketsRepository($c->get(EntityManager::class));
    };

    $container['App\Infrastructure\Persistence\OmegaMesuRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\OmegaMesuRepository($c->get(EntityManager::class));
    };

    // Use Cases (Services)
    $container['App\Application\UseCase\FiltrosService'] = function ($c) {
        return new \App\Application\UseCase\FiltrosService(
            $c->get('App\Infrastructure\Persistence\EstruturaRepository'),
            $c->get('App\Infrastructure\Persistence\StatusIndicadoresRepository')
        );
    };

    $container['App\Application\UseCase\ResumoService'] = function ($c) {
        return new \App\Application\UseCase\ResumoService(
            $c->get('App\Infrastructure\Persistence\RealizadoRepository'),
            $c->get('App\Infrastructure\Persistence\MetaRepository')
        );
    };

    $container['App\Application\UseCase\StatusIndicadoresService'] = function ($c) {
        return new \App\Application\UseCase\StatusIndicadoresService(
            $c->get('App\Infrastructure\Persistence\StatusIndicadoresRepository')
        );
    };

    $container['App\Application\UseCase\AgentService'] = function ($c) {
        return new \App\Application\UseCase\AgentService();
    };

    $container['App\Application\UseCase\OmegaUsersService'] = function ($c) {
        return new \App\Application\UseCase\OmegaUsersService(
            $c->get('App\Infrastructure\Persistence\OmegaUsersRepository')
        );
    };

    $container['App\Application\UseCase\OmegaStatusService'] = function ($c) {
        return new \App\Application\UseCase\OmegaStatusService(
            $c->get('App\Infrastructure\Persistence\OmegaStatusRepository')
        );
    };

    $container['App\Application\UseCase\OmegaStructureService'] = function ($c) {
        return new \App\Application\UseCase\OmegaStructureService(
            $c->get('App\Infrastructure\Persistence\OmegaStructureRepository')
        );
    };

    $container['App\Application\UseCase\OmegaTicketsService'] = function ($c) {
        return new \App\Application\UseCase\OmegaTicketsService(
            $c->get('App\Infrastructure\Persistence\OmegaTicketsRepository')
        );
    };

    $container['App\Application\UseCase\OmegaMesuService'] = function ($c) {
        return new \App\Application\UseCase\OmegaMesuService(
            $c->get('App\Infrastructure\Persistence\OmegaMesuRepository')
        );
    };

    $container['App\Application\UseCase\DetalhesService'] = function ($c) {
        return new \App\Application\UseCase\DetalhesService(
            $c->get('App\Infrastructure\Persistence\DetalhesRepository')
        );
    };

    $container['App\Application\UseCase\EstruturaService'] = function ($c) {
        return new \App\Application\UseCase\EstruturaService(
            $c->get('App\Infrastructure\Persistence\EstruturaRepository')
        );
    };

    $container['App\Application\UseCase\LeadsService'] = function ($c) {
        return new \App\Application\UseCase\LeadsService(
            $c->get('App\Infrastructure\Persistence\LeadsRepository')
        );
    };

    $container['App\Application\UseCase\RealizadoService'] = function ($c) {
        return new \App\Application\UseCase\RealizadoService(
            $c->get('App\Infrastructure\Persistence\RealizadoRepository')
        );
    };

    $container['App\Application\UseCase\VariavelService'] = function ($c) {
        return new \App\Application\UseCase\VariavelService(
            $c->get('App\Infrastructure\Persistence\VariavelRepository')
        );
    };

    $container['App\Application\UseCase\CampanhasService'] = function ($c) {
        return new \App\Application\UseCase\CampanhasService(
            $c->get('App\Infrastructure\Persistence\CampanhasRepository')
        );
    };

    $container['App\Application\UseCase\ProdutoService'] = function ($c) {
        return new \App\Application\UseCase\ProdutoService(
            $c->get('App\Infrastructure\Persistence\ProdutoRepository')
        );
    };

    $container['App\Application\UseCase\MetaService'] = function ($c) {
        return new \App\Application\UseCase\MetaService(
            $c->get('App\Infrastructure\Persistence\MetaRepository')
        );
    };

    $container['App\Application\UseCase\HistoricoService'] = function ($c) {
        return new \App\Application\UseCase\HistoricoService(
            $c->get('App\Infrastructure\Persistence\HistoricoRepository')
        );
    };

    $container['App\Application\UseCase\CalendarioService'] = function ($c) {
        return new \App\Application\UseCase\CalendarioService(
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

