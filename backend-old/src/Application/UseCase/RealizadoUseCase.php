<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\RealizadosRepository;

class RealizadoUseCase extends AbstractUseCase
{
    public function __construct(RealizadosRepository $repository)
    {
        parent::__construct($repository);
    }

}

