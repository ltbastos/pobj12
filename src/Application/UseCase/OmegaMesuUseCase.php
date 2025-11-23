<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\OmegaMesuRepository;

/**
 * UseCase para operações relacionadas a estrutura Omega Mesu
 */
class OmegaMesuUseCase extends AbstractUseCase
{
    /**
     * @param OmegaMesuRepository $repository
     */
    public function __construct(OmegaMesuRepository $repository)
    {
        parent::__construct($repository);
    }
}

