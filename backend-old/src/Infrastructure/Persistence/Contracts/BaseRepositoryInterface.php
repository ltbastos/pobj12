<?php

namespace App\Infrastructure\Persistence\Contracts;

use App\Domain\DTO\FilterDTO;

interface BaseRepositoryInterface
{
    public function baseSelect(): string;
    public function builderFilter(FilterDTO $filters = null): array;
    public function mapToDto(array $row);
    public function fetch(FilterDTO $filters = null): array;
}
