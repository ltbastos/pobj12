<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaStatusRepository;

class OmegaStatusService
{
    private $repository;

    public function __construct(OmegaStatusRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllStatus(): array
    {
        return $this->repository->findAllAsArray();
    }
}

