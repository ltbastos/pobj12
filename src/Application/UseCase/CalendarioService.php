<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\CalendarioRepository;

class CalendarioService
{
    private $repository;

    public function __construct(CalendarioRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCalendario(): array
    {
        return $this->repository->findAllAsArray();
    }
}

