<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\OmegaStructureDTO;
use App\Domain\Enum\Tables;

class OmegaStructureRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(): array
    {
        $sql = "SELECT 
                    departamento,
                    tipo,
                    departamento_id,
                    ordem_departamento,
                    ordem_tipo
                FROM " . Tables::OMEGA_DEPARTAMENTOS . "
                ORDER BY ordem_departamento ASC, ordem_tipo ASC, departamento ASC, tipo ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dto = new OmegaStructureDTO(
                isset($row['departamento']) ? $row['departamento'] : null,
                isset($row['tipo']) ? $row['tipo'] : null,
                isset($row['departamento_id']) ? $row['departamento_id'] : null,
                isset($row['ordem_departamento']) ? $row['ordem_departamento'] : null,
                isset($row['ordem_tipo']) ? $row['ordem_tipo'] : null
            );
            
            return $dto->toArray();
        }, $results);
    }
}
