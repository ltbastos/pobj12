<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\FindAllRealizadosRepository;

/**
 * UseCase para operações relacionadas a realizados
 */
class RealizadoUseCase
{
    /** @var FindAllRealizadosRepository */
    private $repository;

    /**
     * @param FindAllRealizadosRepository $repository
     */
    public function __construct(FindAllRealizadosRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Retorna todos os realizados com filtros opcionais
     * @param FilterDTO|null $filters
     * @return array
     */
    public function getAllRealizados(FilterDTO $filters = null): array
    {
        return $this->repository->fetch($filters);
    }
}

