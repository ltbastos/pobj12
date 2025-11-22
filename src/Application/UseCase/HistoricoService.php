<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\HistoricoRepository;

class HistoricoService
{
    private $repository;

    public function __construct(HistoricoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllHistorico(): array
    {
        return $this->repository->findAllAsArray();
    }
}

