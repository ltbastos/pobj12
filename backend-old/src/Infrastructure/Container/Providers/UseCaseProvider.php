<?php

namespace App\Infrastructure\Container\Providers;

use App\Infrastructure\Container\ServiceProviderInterface;
use App\Infrastructure\Container\ContainerHelper;

class UseCaseProvider implements ServiceProviderInterface
{
    public function register(\Pimple\Container $container)
    {
        $standardUseCases = [
            \App\Application\UseCase\StatusIndicadoresUseCase::class,
            \App\Application\UseCase\OmegaUsersUseCase::class,
            \App\Application\UseCase\OmegaStatusUseCase::class,
            \App\Application\UseCase\OmegaStructureUseCase::class,
            \App\Application\UseCase\OmegaTicketsUseCase::class,
            \App\Application\UseCase\DetalhesUseCase::class,
            \App\Application\UseCase\EstruturaUseCase::class,
            \App\Application\UseCase\LeadsUseCase::class,
            \App\Application\UseCase\RealizadoUseCase::class,
            \App\Application\UseCase\VariavelUseCase::class,
            \App\Application\UseCase\CampanhasUseCase::class,
            \App\Application\UseCase\ProdutoUseCase::class,
            \App\Application\UseCase\MetaUseCase::class,
            \App\Application\UseCase\MesuUseCase::class,
            \App\Application\UseCase\HistoricoUseCase::class,
            \App\Application\UseCase\CalendarioUseCase::class,
            \App\Application\UseCase\PontosUseCase::class,
            \App\Application\UseCase\RankingUseCase::class,
        ];
        
        ContainerHelper::registerMany($container, $standardUseCases);
        
        if (class_exists(\App\Application\UseCase\OmegaMesuUseCase::class)) {
            ContainerHelper::register($container, \App\Application\UseCase\OmegaMesuUseCase::class);
        }
        
        $container[\App\Application\UseCase\ResumoUseCase::class] = function ($c) {
            return new \App\Application\UseCase\ResumoUseCase(
                $c->get(\App\Application\UseCase\ProdutoUseCase::class),
                $c->get(\App\Application\UseCase\VariavelUseCase::class),
                $c->get(\App\Application\UseCase\CalendarioUseCase::class)
            );
        };

        $container[\App\Application\UseCase\FiltrosUseCase::class] = function ($c) {
            return new \App\Application\UseCase\FiltrosUseCase(
                $c->get(\App\Infrastructure\Persistence\EstruturaRepository::class),
                $c->get(\App\Infrastructure\Persistence\StatusIndicadoresRepository::class)
            );
        };
        
        $container[\App\Application\UseCase\AgentUseCase::class] = function ($c) {
            return new \App\Application\UseCase\AgentUseCase();
        };
    }
}

