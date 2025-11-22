<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaTicketsRepository;

class OmegaTicketsUseCase
{
    private $repository;

    public function __construct(OmegaTicketsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllTickets(): array
    {
        return $this->repository->findAllAsArray();
    }
}

