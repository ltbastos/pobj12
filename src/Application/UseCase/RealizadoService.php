<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\RealizadoRepository;

class RealizadoService
{
    private $repository;

    public function __construct(RealizadoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllRealizados(): array
    {
        return $this->repository->findAllAsArray();
    }
}

