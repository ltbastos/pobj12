<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\RankingRepository;

class RankingUseCase extends AbstractUseCase
{
    public function __construct(RankingRepository $repository)
    {
        parent::__construct($repository);
    }
}

