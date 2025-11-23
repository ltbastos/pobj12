<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaUsersRepository;

/**
 * UseCase para operações relacionadas a usuários Omega
 */
class OmegaUsersUseCase extends AbstractUseCase
{
    /**
     * @param OmegaUsersRepository $repository
     */
    public function __construct(OmegaUsersRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Retorna todos os usuários Omega
     * @return array
     */
    public function getAllUsers(): array
    {
        return $this->repository->fetch(null);
    }
}

