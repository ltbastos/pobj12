<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\MetasRepository;

/**
 * UseCase para operações relacionadas a metas
 */
class MetaUseCase extends AbstractUseCase
{
    public function __construct(MetasRepository $repository)
    {
        parent::__construct($repository);
    }
}

