<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\EstruturaRepository;

class EstruturaUseCase
{
    private $repository;

    public function __construct(EstruturaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllEstrutura(): array
    {
        return [
            'segmentos'       => $this->repository->findAllSegmentos(),
            'diretorias'      => $this->repository->findAllDiretorias(),
            'regionais'       => $this->repository->findAllRegionais(),
            'agencias'        => $this->repository->findAllAgencias(),
            'gerentes_gestao' => $this->repository->findAllGGestoes(),
            'gerentes'        => $this->repository->findGerentesWithGestor(),
        ];
    }
}

