<?php

namespace App\Application\UseCase\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Repository\Pobj\FHistoricoRankingPobjRepository;

class RankingUseCase
{
    private $repository;

    public function __construct(FHistoricoRankingPobjRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(?FilterDTO $filters = null): array
    {
        return $this->repository->findRankingWithFilters($filters);
    }
}

