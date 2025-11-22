<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\MetaRepository;

class MetaService
{
    private $repository;

    public function __construct(MetaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllMetas(): array
    {
        return $this->repository->findAllAsArray();
    }
}

