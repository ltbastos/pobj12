<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\PontosRepository;

class PontosUseCase
{
    private $repository;

    public function __construct(PontosRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllPontos(FilterDTO $filters = null): array
    {
        return $this->repository->findAllAsArray($filters);
    }
}

