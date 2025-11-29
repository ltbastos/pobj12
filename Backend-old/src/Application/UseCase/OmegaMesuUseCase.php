<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaMesuRepository;

class OmegaMesuUseCase extends AbstractUseCase
{
    public function __construct(OmegaMesuRepository $repository)
    {
        parent::__construct($repository);
    }
}

