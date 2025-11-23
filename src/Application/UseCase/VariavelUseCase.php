<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\VariavelRepository;

class VariavelUseCase
{
    private $repository;

    public function __construct(VariavelRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllVariaveis(FilterDTO $filters = null): array
    {
        return $this->repository->findAllAsArray($filters);
    }
}

