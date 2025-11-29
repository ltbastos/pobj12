<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaMesuRepository;

class MesuUseCase extends AbstractUseCase
{
    public function __construct(OmegaMesuRepository $repository)
    {
        parent::__construct($repository);
    }
}

