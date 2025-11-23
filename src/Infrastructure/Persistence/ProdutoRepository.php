<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\Entity\DProduto;
use App\Domain\Enum\Tables;

class ProdutoRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(FilterDTO $filters = null): array
    {
        $sql = "SELECT 
                    id,
                    id_familia,
                    familia,
                    id_indicador,
                    indicador,
                    id_subindicador,
                    subindicador,
                    metrica,
                    MAX(peso) as peso
                FROM " . Tables::D_PRODUTOS . "
                WHERE 1=1";
        
        $params = [];
        
        if ($filters !== null && $filters->hasAnyFilter()) {
            if ($filters->familia !== null) {
                $sql .= " AND id_familia = :familia";
                $params[':familia'] = $filters->familia;
            }
            if ($filters->indicador !== null) {
                $sql .= " AND id_indicador = :indicador";
                $params[':indicador'] = $filters->indicador;
            }
            if ($filters->subindicador !== null) {
                $sql .= " AND id_subindicador = :subindicador";
                $params[':subindicador'] = $filters->subindicador;
            }
        }
        
        $sql .= " GROUP BY 
                    id,
                    id_familia,
                    familia,
                    id_indicador,
                    indicador,
                    id_subindicador,
                    subindicador,
                    metrica
                ORDER BY familia ASC, indicador ASC, subindicador ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $entity = DProduto::fromArray($row);
            return $entity->toDTO()->toArray();
        }, $results);
    }
}
