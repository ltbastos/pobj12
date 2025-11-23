<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\OmegaStatusDTO;
use App\Domain\Enum\Tables;

class OmegaStatusRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(): array
    {
        $sql = "SELECT 
                    id,
                    label,
                    tone,
                    descricao,
                    ordem,
                    departamento_id
                FROM " . Tables::OMEGA_STATUS . "
                ORDER BY ordem ASC, label ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dto = new OmegaStatusDTO(
                isset($row['id']) ? $row['id'] : null,
                isset($row['label']) ? $row['label'] : null,
                isset($row['tone']) ? $row['tone'] : null,
                isset($row['descricao']) ? $row['descricao'] : null,
                isset($row['ordem']) ? $row['ordem'] : null,
                isset($row['departamento_id']) ? $row['departamento_id'] : null
            );
            
            return $dto->toArray();
        }, $results);
    }
}
