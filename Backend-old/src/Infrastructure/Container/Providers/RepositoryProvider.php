<?php

namespace App\Infrastructure\Container\Providers;

use App\Infrastructure\Container\ServiceProviderInterface;
use App\Infrastructure\Container\ContainerHelper;

class RepositoryProvider implements ServiceProviderInterface
{
    public function register(\Pimple\Container $container)
    {
        $repositories = [
            \App\Infrastructure\Persistence\EstruturaRepository::class,
            \App\Infrastructure\Persistence\StatusIndicadoresRepository::class,
            \App\Infrastructure\Persistence\RealizadosRepository::class,
            \App\Infrastructure\Persistence\MetasRepository::class,
            \App\Infrastructure\Persistence\VariavelRepository::class,
            \App\Infrastructure\Persistence\ProdutoRepository::class,
            \App\Infrastructure\Persistence\CalendarioRepository::class,
            \App\Infrastructure\Persistence\CampanhasRepository::class,
            \App\Infrastructure\Persistence\DetalhesRepository::class,
            \App\Infrastructure\Persistence\HistoricoRepository::class,
            \App\Infrastructure\Persistence\LeadsRepository::class,
            \App\Infrastructure\Persistence\OmegaUsersRepository::class,
            \App\Infrastructure\Persistence\OmegaStatusRepository::class,
            \App\Infrastructure\Persistence\OmegaStructureRepository::class,
            \App\Infrastructure\Persistence\OmegaTicketsRepository::class,
            \App\Infrastructure\Persistence\OmegaMesuRepository::class,
            \App\Infrastructure\Persistence\PontosRepository::class,
            \App\Infrastructure\Persistence\RankingRepository::class,
        ];
        
        ContainerHelper::registerMany($container, $repositories);
    }
}

