<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\VariavelRepository;

class VariavelUseCase extends AbstractUseCase
{
    public function __construct(VariavelRepository $repository)
    {
        parent::__construct($repository);
    }
} 

