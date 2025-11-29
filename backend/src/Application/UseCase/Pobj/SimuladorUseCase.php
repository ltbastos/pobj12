<?php

namespace App\Application\UseCase\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Repository\Pobj\SimuladorRepository;

class SimuladorUseCase
{
    private $simuladorRepository;

    public function __construct(SimuladorRepository $simuladorRepository)
    {
        $this->simuladorRepository = $simuladorRepository;
    }

    /**
     * Retorna os produtos com dados para o simulador
     */
    public function handle(?FilterDTO $filters = null): array
    {
        return $this->simuladorRepository->findProdutosForSimulador($filters);
    }
}

