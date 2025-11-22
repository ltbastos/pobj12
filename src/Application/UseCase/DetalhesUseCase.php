<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\DetalhesRepository;

class DetalhesUseCase
{
    private $repository;

    public function __construct(DetalhesRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllDetalhes(): array
    {
        return $this->repository->findAllAsArray();
    }
}

