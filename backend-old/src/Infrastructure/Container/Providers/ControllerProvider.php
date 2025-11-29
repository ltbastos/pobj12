<?php

namespace App\Infrastructure\Container\Providers;

use App\Infrastructure\Container\ServiceProviderInterface;
use App\Infrastructure\Container\ContainerHelper;

class ControllerProvider implements ServiceProviderInterface
{
    public function register(\Pimple\Container $container)
    {
        $standardControllers = [
            \App\Presentation\Controllers\AgentController::class,
            \App\Presentation\Controllers\CalendarioController::class,
            \App\Presentation\Controllers\CampanhasController::class,
            \App\Presentation\Controllers\DetalhesController::class,
            \App\Presentation\Controllers\EstruturaController::class,
            \App\Presentation\Controllers\MesuController::class,
            \App\Presentation\Controllers\FiltrosController::class,
            \App\Presentation\Controllers\HistoricoController::class,
            \App\Presentation\Controllers\ResumoController::class,
            \App\Presentation\Controllers\LeadsController::class,
            \App\Presentation\Controllers\MetasController::class,
            \App\Presentation\Controllers\ProdutosController::class,
            \App\Presentation\Controllers\RealizadosController::class,
            \App\Presentation\Controllers\StatusIndicadoresController::class,
            \App\Presentation\Controllers\VariavelController::class,
            \App\Presentation\Controllers\PontosController::class,
            \App\Presentation\Controllers\RankingController::class,
            \App\Presentation\Controllers\Omega\OmegaMesuController::class,
            \App\Presentation\Controllers\Omega\OmegaStatusController::class,
            \App\Presentation\Controllers\Omega\OmegaStructureController::class,
            \App\Presentation\Controllers\Omega\OmegaTicketsController::class,
            \App\Presentation\Controllers\Omega\OmegaUsersController::class,
        ];
        
        ContainerHelper::registerMany($container, $standardControllers);
        
        $container[\App\Presentation\Controllers\HealthController::class] = function ($c) {
            return new \App\Presentation\Controllers\HealthController();
        };
    }
}

