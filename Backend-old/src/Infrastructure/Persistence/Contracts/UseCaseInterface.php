<?php

namespace App\Infrastructure\Persistence\Contracts;

use App\Domain\DTO\FilterDTO;

interface UseCaseInterface
{
    public function handle(FilterDTO $filters = null): array;
}