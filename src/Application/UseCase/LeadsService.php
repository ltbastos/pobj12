<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\LeadsRepository;

class LeadsService
{
    private $repository;

    public function __construct(LeadsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllLeads(): array
    {
        return $this->repository->findAllAsArray();
    }
}

