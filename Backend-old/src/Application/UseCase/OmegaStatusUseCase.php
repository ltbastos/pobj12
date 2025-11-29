<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaStatusRepository;

/**
 * UseCase para operações relacionadas a status Omega
 */
class OmegaStatusUseCase extends AbstractUseCase
{
    /**
     * @param OmegaStatusRepository $repository
     */
    public function __construct(OmegaStatusRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Retorna todos os status Omega
     * @return array
     */
    public function getAllStatus(): array
    {
        return $this->repository->fetch(null);
    }
}

