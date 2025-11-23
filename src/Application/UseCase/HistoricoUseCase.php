<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\HistoricoRepository;

class HistoricoUseCase
{
    private $repository;

    public function __construct(HistoricoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllHistorico(FilterDTO $filters = null): array
    {
        return $this->repository->findAllAsArray($filters);
    }
}

