<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\ProdutoRepository;

class ProdutoUseCase
{
    private $repository;

    public function __construct(ProdutoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllProdutos(FilterDTO $filters = null): array
    {
        return $this->repository->findAllAsArray($filters);
    }
}

