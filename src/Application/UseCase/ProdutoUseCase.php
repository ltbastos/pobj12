<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\ProdutoRepository;

class ProdutoUseCase
{
    private $repository;

    public function __construct(ProdutoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllProdutos(): array
    {
        return $this->repository->findAllAsArray();
    }
}

