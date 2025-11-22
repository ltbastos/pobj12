<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaStructureRepository;

class OmegaStructureService
{
    private $repository;

    public function __construct(OmegaStructureRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getStructure(): array
    {
        return $this->repository->findAllAsArray();
    }
}

