<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\Enum\Tables;
use App\Domain\ValueObject\StatusIndicador;

class StatusIndicadoresRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(): array
    {
        $sql = "SELECT id, status AS label
                FROM " . Tables::D_STATUS_INDICADORES . "
                ORDER BY id ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            return StatusIndicador::getDefaults();
        }

        return $results;
    }

    public function findAllForFilter(): array
    {
        $sql = "SELECT id, status AS label
                FROM " . Tables::D_STATUS_INDICADORES . "
                ORDER BY id ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            return StatusIndicador::getDefaultsForFilter();
        }

        return $results;
    }
}
