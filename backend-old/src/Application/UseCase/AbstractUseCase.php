<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\Contracts\UseCaseInterface;
use App\Infrastructure\Persistence\BaseRepository;
use App\Domain\DTO\FilterDTO;

abstract class AbstractUseCase implements UseCaseInterface
{
    protected $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(FilterDTO $filters = null): array
    {
        return $this->repository->fetch($filters);
    }
}