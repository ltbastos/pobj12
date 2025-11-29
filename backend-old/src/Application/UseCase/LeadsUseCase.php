<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\LeadsRepository;

class LeadsUseCase extends AbstractUseCase
{
    public function __construct(LeadsRepository $repository)
    {
        parent::__construct($repository);
    }
}

