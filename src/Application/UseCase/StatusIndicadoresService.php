<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\StatusIndicadoresRepository;

class StatusIndicadoresService
{
    private $statusRepository;

    public function __construct(StatusIndicadoresRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    public function getAllStatus(): array
    {
        return $this->statusRepository->findAllAsArray();
    }
}

