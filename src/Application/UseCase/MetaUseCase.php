<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\MetaRepository;

class MetaUseCase
{
    private $repository;

    public function __construct(MetaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllMetas(FilterDTO $filters = null): array
    {
        return $this->repository->findAllAsArray($filters);
    }
}

