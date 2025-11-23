<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\OmegaMesuRepository;

class OmegaMesuUseCase
{
    private $repository;

    public function __construct(OmegaMesuRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getMesuData(FilterDTO $filters = null): array
    {
        return $this->repository->findAll($filters);
    }
}

