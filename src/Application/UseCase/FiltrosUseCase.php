<?php

namespace App\Application\UseCase;

use App\Domain\ValueObject\FiltroNivel;
use App\Infrastructure\Persistence\EstruturaRepository;
use App\Infrastructure\Persistence\StatusIndicadoresRepository;

class FiltrosUseCase
{
    private $estruturaRepository;
    private $statusRepository;

    public function __construct(
        EstruturaRepository $estruturaRepository,
        StatusIndicadoresRepository $statusRepository
    ) {
        $this->estruturaRepository = $estruturaRepository;
        $this->statusRepository = $statusRepository;
    }

    public function getFiltroByNivel($nivel): array
    {
        // Compatibilidade com PHP 7.1 - aceita string
        $nivelValue = is_string($nivel) ? $nivel : (string)$nivel;
        
        switch ($nivelValue) {
            case FiltroNivel::SEGMENTOS:
                return $this->estruturaRepository->findAllSegmentos();
            case FiltroNivel::DIRETORIAS:
                return $this->estruturaRepository->findAllDiretorias();
            case FiltroNivel::REGIONAIS:
                return $this->estruturaRepository->findAllRegionais();
            case FiltroNivel::AGENCIAS:
                return $this->estruturaRepository->findAllAgencias();
            case FiltroNivel::GGESTOES:
                return $this->estruturaRepository->findGGestoesForFilter();
            case FiltroNivel::GERENTES:
                return $this->estruturaRepository->findGerentesForFilter();
            case FiltroNivel::STATUS_INDICADORES:
                return $this->statusRepository->findAllForFilter();
            default:
                throw new \InvalidArgumentException('Nível inválido: ' . $nivelValue);
        }
    }
}

