<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\VariavelRepository;

class VariavelService
{
    private $repository;

    public function __construct(VariavelRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllVariaveis(): array
    {
        return $this->repository->findAllAsArray();
    }
}

