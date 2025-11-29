<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\DetalhesRepository;

class DetalhesUseCase extends AbstractUseCase
{
    public function __construct(DetalhesRepository $repository)
    {
        parent::__construct($repository);
    }
}

