<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaTicketsRepository;

/**
 * UseCase para operações relacionadas a tickets Omega
 */
class OmegaTicketsUseCase extends AbstractUseCase
{
    /**
     * @param OmegaTicketsRepository $repository
     */
    public function __construct(OmegaTicketsRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Retorna todos os tickets Omega
     * @return array
     */
    public function getAllTickets(): array
    {
        return $this->repository->fetch(null);
    }
}

