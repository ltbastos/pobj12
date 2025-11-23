<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\Interface\BaseRepositoryInterface;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected $pdo;
    protected $dtoClass;

    public function __construct(PDO $pdo, string $dtoClass)
    {
        $this->pdo = $pdo;
        $this->dtoClass = $dtoClass;
    }

    /**
     * Mapeia um array de resultados para DTO
     * Deve ser implementado pelos repositórios filhos
     * @param array $row
     * @return mixed
     */
    abstract public function mapToDto(array $row);

    protected function getOrderBy(): string
    {
        return "";
    }

    public function fetch(FilterDTO $filters = null): array
    {
        $sql = $this->baseSelect();
        $filterResult = $this->builderFilter($filters);
        
        $sql .= $filterResult['sql'];
        $orderBy = $this->getOrderBy();
        if ($orderBy !== "") {
            $sql .= " " . $orderBy;
        }
        $params = $filterResult['params'];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dto = $this->mapToDto($row);
            return method_exists($dto, 'toArray') ? $dto->toArray() : $dto;
        }, $results);
    }
}
