<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaMesuRepository;

class OmegaMesuService
{
    private $repository;

    public function __construct(OmegaMesuRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getMesuData(): array
    {
        return $this->repository->findAll();
    }
}

