<?php

return function ($container) {
    registerDatabase($container);
    registerRepositories($container);
    registerUseCases($container);
    registerControllers($container);
};

function registerDatabase($container)
{

    $container[PDO::class] = function ($c) {
        $settings = $c->get('settings')['db'];
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $settings['driver'],
            $settings['host'],
            $settings['port'],
            $settings['database'],
            $settings['charset']
        );
        
        try {
            // Criar instância PDO com opções otimizadas para reduzir conexões desnecessárias
            $pdo = new PDO($dsn, $settings['username'], $settings['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false, // Não usar conexões persistentes para evitar problemas com limites
                PDO::ATTR_EMULATE_PREPARES => false, // Usar prepared statements nativos do MySQL (mais eficiente)
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_TIMEOUT => 5, // Timeout de conexão de 5 segundos
            ]);
            
            return $pdo;
        } catch (\PDOException $e) {
            // Tratamento específico para erro de limite de conexões
            if ($e->getCode() == 1226 || strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
                error_log(sprintf(
                    'ERRO: Limite de conexões por hora excedido. Código: %s, Mensagem: %s',
                    $e->getCode(),
                    $e->getMessage()
                ));
                
                // Lançar exceção com mensagem mais clara
                throw new \PDOException(
                    'Limite de conexões ao banco de dados excedido. Por favor, tente novamente em alguns minutos. ' .
                    'Se o problema persistir, entre em contato com o administrador do sistema.',
                    1226,
                    $e
                );
            }
            
            // Re-lançar outras exceções PDO
            throw $e;
        }
    };
}

function registerRepositories($container)
{
    $container['App\Infrastructure\Persistence\EstruturaRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\EstruturaRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\StatusIndicadoresRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\StatusIndicadoresRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\RealizadosRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\RealizadosRepository($c->get(PDO::class));
    };

    $container['App\Infrastructure\Persistence\MetasRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\MetasRepository($c->get(PDO::class));
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

    $container['App\Infrastructure\Persistence\PontosRepository'] = function ($c) {
        return new \App\Infrastructure\Persistence\PontosRepository($c->get(PDO::class));
    };
}

function registerUseCases($container)
{
    $container['App\Application\UseCase\FiltrosUseCase'] = function ($c) {
        return new \App\Application\UseCase\FiltrosUseCase(
            $c->get('App\Infrastructure\Persistence\EstruturaRepository'),
            $c->get('App\Infrastructure\Persistence\StatusIndicadoresRepository')
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
            $c->get('App\Infrastructure\Persistence\RealizadosRepository')
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
            $c->get('App\Infrastructure\Persistence\MetasRepository')
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

    $container['App\Application\UseCase\PontosUseCase'] = function ($c) {
        return new \App\Application\UseCase\PontosUseCase(
            $c->get('App\Infrastructure\Persistence\PontosRepository')
        );
    };
}

function registerControllers($container)
{
    $container['App\Presentation\Controllers\HealthController'] = function ($c) {
        return new \App\Presentation\Controllers\HealthController($c->get(PDO::class));
    };

    $container['App\Presentation\Controllers\AgentController'] = function ($c) {
        return new \App\Presentation\Controllers\AgentController(
            $c->get('App\Application\UseCase\AgentUseCase')
        );
    };

    $container['App\Presentation\Controllers\CalendarioController'] = function ($c) {
        return new \App\Presentation\Controllers\CalendarioController(
            $c->get('App\Application\UseCase\CalendarioUseCase')
        );
    };

    $container['App\Presentation\Controllers\CampanhasController'] = function ($c) {
        return new \App\Presentation\Controllers\CampanhasController(
            $c->get('App\Application\UseCase\CampanhasUseCase')
        );
    };

    $container['App\Presentation\Controllers\DetalhesController'] = function ($c) {
        return new \App\Presentation\Controllers\DetalhesController(
            $c->get('App\Application\UseCase\DetalhesUseCase')
        );
    };

    $container['App\Presentation\Controllers\EstruturaController'] = function ($c) {
        return new \App\Presentation\Controllers\EstruturaController(
            $c->get('App\Application\UseCase\EstruturaUseCase')
        );
    };

    $container['App\Presentation\Controllers\FiltrosController'] = function ($c) {
        return new \App\Presentation\Controllers\FiltrosController(
            $c->get('App\Application\UseCase\FiltrosUseCase')
        );
    };

    $container['App\Presentation\Controllers\HistoricoController'] = function ($c) {
        return new \App\Presentation\Controllers\HistoricoController(
            $c->get('App\Application\UseCase\HistoricoUseCase')
        );
    };

    $container['App\Presentation\Controllers\LeadsController'] = function ($c) {
        return new \App\Presentation\Controllers\LeadsController(
            $c->get('App\Application\UseCase\LeadsUseCase')
        );
    };

    $container['App\Presentation\Controllers\MetasController'] = function ($c) {
        return new \App\Presentation\Controllers\MetasController(
            $c->get('App\Application\UseCase\MetaUseCase')
        );
    };

    $container['App\Presentation\Controllers\RealizadosController'] = function ($c) {
        return new \App\Presentation\Controllers\RealizadosController(
            $c->get('App\Application\UseCase\RealizadoUseCase')
        );
    };

    $container['App\Presentation\Controllers\ProdutosController'] = function ($c) {
        return new \App\Presentation\Controllers\ProdutosController(
            $c->get('App\Application\UseCase\ProdutoUseCase')
        );
    };

    $container['App\Presentation\Controllers\StatusIndicadoresController'] = function ($c) {
        return new \App\Presentation\Controllers\StatusIndicadoresController(
            $c->get('App\Application\UseCase\StatusIndicadoresUseCase')
        );
    };

    $container['App\Presentation\Controllers\VariavelController'] = function ($c) {
        return new \App\Presentation\Controllers\VariavelController(
            $c->get('App\Application\UseCase\VariavelUseCase')
        );
    };

    $container['App\Presentation\Controllers\PontosController'] = function ($c) {
        return new \App\Presentation\Controllers\PontosController(
            $c->get('App\Application\UseCase\PontosUseCase')
        );
    };

    $container['App\Presentation\Controllers\MesuController'] = function ($c) {
        return new \App\Presentation\Controllers\MesuController(
            $c->get('App\Application\UseCase\OmegaMesuUseCase')
        );
    };

    $container['App\Presentation\Controllers\OmegaMesuController'] = function ($c) {
        return new \App\Presentation\Controllers\OmegaMesuController(
            $c->get('App\Application\UseCase\OmegaMesuUseCase')
        );
    };

    $container['App\Presentation\Controllers\OmegaStatusController'] = function ($c) {
        return new \App\Presentation\Controllers\OmegaStatusController(
            $c->get('App\Application\UseCase\OmegaStatusUseCase')
        );
    };

    $container['App\Presentation\Controllers\OmegaStructureController'] = function ($c) {
        return new \App\Presentation\Controllers\OmegaStructureController(
            $c->get('App\Application\UseCase\OmegaStructureUseCase')
        );
    };

    $container['App\Presentation\Controllers\OmegaTicketsController'] = function ($c) {
        return new \App\Presentation\Controllers\OmegaTicketsController(
            $c->get('App\Application\UseCase\OmegaTicketsUseCase')
        );
    };

    $container['App\Presentation\Controllers\OmegaUsersController'] = function ($c) {
        return new \App\Presentation\Controllers\OmegaUsersController(
            $c->get('App\Application\UseCase\OmegaUsersUseCase')
        );
    };
}
