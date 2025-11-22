<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\CampanhasRepository;

class CampanhasService
{
    private $repository;

    public function __construct(CampanhasRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCampanhas(): array
    {
        return $this->repository->findAllAsArray();
    }
}

