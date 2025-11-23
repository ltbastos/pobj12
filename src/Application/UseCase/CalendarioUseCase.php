<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\FindAllCalendarioRepository;

/**
 * UseCase para operações relacionadas ao calendário
 */
class CalendarioUseCase
{
    /** @var FindAllCalendarioRepository */
    private $repository;

    /**
     * @param FindAllCalendarioRepository $repository
     */
    public function __construct(FindAllCalendarioRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Retorna todos os registros do calendário
     * @return array
     */
    public function getAllCalendario(): array
    {
        return $this->repository->fetchAll();
    }
}

