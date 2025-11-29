<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\PontosRepository;

class PontosUseCase extends AbstractUseCase
{
    public function __construct(PontosRepository $repository)
    {
        parent::__construct($repository);
    }
}

