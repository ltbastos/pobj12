<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\HistoricoRepository;

class HistoricoUseCase extends AbstractUseCase
{
    public function __construct(HistoricoRepository $repository)
    {
        parent::__construct($repository);
    }
}

