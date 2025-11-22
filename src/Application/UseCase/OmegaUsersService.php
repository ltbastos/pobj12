<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaUsersRepository;

class OmegaUsersService
{
    private $repository;

    public function __construct(OmegaUsersRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllUsers(): array
    {
        return $this->repository->findAllAsArray();
    }
}

