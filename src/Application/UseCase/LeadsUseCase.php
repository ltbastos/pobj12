<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\LeadsRepository;

class LeadsUseCase
{
    private $repository;

    public function __construct(LeadsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllLeads(FilterDTO $filters = null): array
    {
        return $this->repository->findAllAsArray($filters);
    }
}

