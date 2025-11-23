<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\CampanhasRepository;

class CampanhasUseCase
{
    private $repository;

    public function __construct(CampanhasRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCampanhas(FilterDTO $filters = null): array
    {
        return $this->repository->findAllAsArray($filters);
    }
}

